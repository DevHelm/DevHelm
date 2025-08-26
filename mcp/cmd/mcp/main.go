package main

import (
	"bufio"
	"bytes"
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"os"
	"time"

	mcp "github.com/mark3labs/mcp-go/mcp"
)

const (
	serverName    = "devhelm-mcp"
	serverVersion = "0.1.0"
)

func getFullURL(endpoint string) string {
	baseURL := os.Getenv("BASE_URL")
	if baseURL == "" {
		fmt.Fprintln(os.Stderr, "BASE_URL environment variable is required but not set")
		os.Exit(1)
	}

	if len(baseURL) > 0 && baseURL[len(baseURL)-1] == '/' {
		baseURL = baseURL[:len(baseURL)-1]
	}

	if len(endpoint) > 0 && endpoint[0] != '/' {
		return baseURL + "/" + endpoint
	}
	return baseURL + endpoint
}

func postAndExtractTitle(ctx context.Context) (string, error) {
	endpoint := os.Getenv("ENDPOINT")
	if endpoint == "" {
		fmt.Fprintln(os.Stderr, "ENDPOINT environment variable is required but not set")
		os.Exit(1)
	}

	body := bytes.NewBufferString("{}")

	fullURL := getFullURL(endpoint)
	req, err := http.NewRequestWithContext(ctx, http.MethodPost, fullURL, body)
	if err != nil {
		return "", err
	}
	req.Header.Set("Content-Type", "application/json")

	apiKey := os.Getenv("API_KEY")
	if apiKey == "" {
		fmt.Fprintln(os.Stderr, "API_KEY environment variable is required but not set")
		os.Exit(1)
	}
	req.Header.Set("X-API-KEY", apiKey)

	client := &http.Client{Timeout: 15 * time.Second}
	res, err := client.Do(req)
	if err != nil {
		return "", err
	}
	defer res.Body.Close()

	if res.StatusCode < 200 || res.StatusCode >= 300 {
		b, _ := io.ReadAll(res.Body)
		return "", fmt.Errorf("unexpected status %d: %s", res.StatusCode, string(b))
	}

	var payload struct {
		Title string `json:"title"`
	}
	dec := json.NewDecoder(res.Body)
	if err := dec.Decode(&payload); err != nil {
		return "", err
	}
	if payload.Title == "" {
		return "", errors.New("empty title in response")
	}
	return payload.Title, nil
}

func handleToolCall(ctx context.Context, req mcp.CallToolRequest) (*mcp.CallToolResult, error) {
	jira, err := req.RequireString("jira_ticket")
	if err != nil || jira == "" {
		return mcp.NewToolResultError("missing required input: jira_ticket"), nil
	}

	title, err := postAndExtractTitle(ctx)
	if err != nil {
		return mcp.NewToolResultErrorFromErr("remote request failed", err), nil
	}

	structured := map[string]any{
		"jira_ticket": jira,
		"title":       title,
	}
	return mcp.NewToolResultStructuredOnly(structured), nil
}

func main() {
	ctx := context.Background()
	
	if os.Getenv("TEST") != "" {
		go func() {
			time.Sleep(5 * time.Second)
			fmt.Fprintln(os.Stderr, "Test timeout reached (5 seconds)")
			os.Exit(1)
		}()
	}
	
	tool := mcp.NewTool(
		"report_done",
		mcp.WithDescription("Marks a JIRA ticket as done by calling a remote endpoint and returns the resulting title."),
		mcp.WithString("jira_ticket", mcp.Description("The JIRA ticket key to report as done (e.g., PROJ-123)"), mcp.Required()),
	)

	tools := []mcp.Tool{tool}

	in := bufio.NewReader(os.Stdin)
	out := bufio.NewWriter(os.Stdout)
	defer out.Flush()

	dec := json.NewDecoder(in)
	enc := json.NewEncoder(out)

	for {
		var raw map[string]any
		if err := dec.Decode(&raw); err != nil {
			if err == io.EOF {
				return
			}
			fmt.Fprintln(os.Stderr, "decode error:", err)
			continue
		}

		method, _ := raw["method"].(string)
		id := mcp.RequestId{}
		if v, ok := raw["id"]; ok {
			id = mcp.NewRequestId(v)
		}

		writeResult := func(result any) {
			resp := mcp.JSONRPCResponse{JSONRPC: mcp.JSONRPC_VERSION, ID: id, Result: result}
			_ = enc.Encode(resp)
			out.Flush()
		}
		writeError := func(code int, msg string, data any) {
			errObj := mcp.NewJSONRPCError(id, code, msg, data)
			_ = enc.Encode(errObj)
			out.Flush()
		}

		switch method {
		case string(mcp.MethodInitialize):
			caps := mcp.ServerCapabilities{Tools: &struct {
				ListChanged bool `json:"listChanged,omitempty"`
			}{ListChanged: false}}
			result := mcp.NewInitializeResult(
				mcp.LATEST_PROTOCOL_VERSION,
				caps,
				mcp.Implementation{Name: serverName, Version: serverVersion},
				"Use the report_done tool to mark JIRA tickets done.",
			)
			writeResult(result)

		case string(mcp.MethodPing):
			writeResult(mcp.EmptyResult{})

		case string(mcp.MethodToolsList):
			writeResult(mcp.NewListToolsResult(tools, ""))

		case string(mcp.MethodToolsCall):
			var params json.RawMessage
			if p, ok := raw["params"]; ok {
				b, _ := json.Marshal(p)
				params = b
			}
			var callReq mcp.CallToolRequest
			if err := json.Unmarshal(params, &callReq); err != nil {
				writeError(mcp.INVALID_PARAMS, "invalid call params", err.Error())
				continue
			}
			if callReq.Params.Name != tool.Name {
				writeError(mcp.METHOD_NOT_FOUND, "unknown tool", callReq.Params.Name)
				continue
			}
			res, _ := handleToolCall(ctx, callReq)
			writeResult(res)

		default:
			writeError(mcp.METHOD_NOT_FOUND, "method not found", method)
		}
	}
}
