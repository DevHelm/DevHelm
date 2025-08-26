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

// Constants holds all constant values used across the server.
const (
	serverName    = "comcontrol-mcp"
	serverVersion = "0.1.0"
)

// Default values if environment variables are not set
var defaultBaseURL = "https://jsonplaceholder.typicode.com"
var defaultEndpoint = "/todos/1"

// getFullURL constructs the full URL by combining BASE_URL from environment variable with the endpoint
func getFullURL(endpoint string) string {
	baseURL := os.Getenv("BASE_URL")
	if baseURL == "" {
		baseURL = defaultBaseURL
	}
	
	// Ensure baseURL doesn't end with a slash and endpoint starts with a slash
	if len(baseURL) > 0 && baseURL[len(baseURL)-1] == '/' && len(endpoint) > 0 && endpoint[0] == '/' {
		return baseURL + endpoint[1:]
	}
	if len(baseURL) > 0 && baseURL[len(baseURL)-1] != '/' && len(endpoint) > 0 && endpoint[0] != '/' {
		return baseURL + "/" + endpoint
	}
	return baseURL + endpoint
}

// postAndExtractTitle performs a POST to the endpoint and returns the title field.
func postAndExtractTitle(ctx context.Context) (string, error) {
	// Typically ignores body for this endpoint; we still send an empty JSON object
	body := bytes.NewBufferString("{}")
	
	fullURL := getFullURL(defaultEndpoint)
	req, err := http.NewRequestWithContext(ctx, http.MethodPost, fullURL, body)
	if err != nil {
		return "", err
	}
	req.Header.Set("Content-Type", "application/json")
	
	// Add X-API-KEY header if API_KEY environment variable is set
	apiKey := os.Getenv("API_KEY")
	if apiKey != "" {
		req.Header.Set("X-API-KEY", apiKey)
	}

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

// handleToolCall executes the report_done tool
func handleToolCall(ctx context.Context, req mcp.CallToolRequest) (*mcp.CallToolResult, error) {
	// Require jira_ticket argument
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

	// Define the tool schema using the current mcp-go API
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
			// ignore malformed input
			fmt.Fprintln(os.Stderr, "decode error:", err)
			continue
		}

		// Extract basic fields
		method, _ := raw["method"].(string)
		id := mcp.RequestId{}
		if v, ok := raw["id"]; ok {
			id = mcp.NewRequestId(v)
		}

		// Prepare helper to write a response
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
			caps := mcp.ServerCapabilities{Tools: &struct{ ListChanged bool `json:"listChanged,omitempty"` }{ListChanged: false}}
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
			// Marshal raw params into CallToolRequest
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
			// Unknown method
			writeError(mcp.METHOD_NOT_FOUND, "method not found", method)
		}
	}
}
