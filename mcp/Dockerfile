# syntax=docker/dockerfile:1

FROM golang:1.22 AS builder
WORKDIR /app

# Pre-cache dependencies
COPY mcp/go.mod ./go.mod
RUN --mount=type=cache,target=/go/pkg/mod \
    go mod download

# Copy source
COPY mcp/ .

# Build static binary
RUN --mount=type=cache,target=/go/pkg/mod \
    CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o /out/mcp-server

FROM gcr.io/distroless/static-debian12
COPY --from=builder /out/mcp-server /mcp-server
USER 65532:65532
ENTRYPOINT ["/mcp-server"]
