#!/usr/bin/env bash

set -euo pipefail

API_URL="${MONITORING_ENDPOINT:-http://127.0.0.1/api/site-status}"
API_TOKEN="${MONITORING_TOKEN:-}"
LOG_FILE="/var/log/docker-monitor.log"

if [[ -z "${API_TOKEN}" ]]; then
    echo "Missing MONITORING_TOKEN environment variable." >&2
    exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
    echo "docker cli is required" >&2
    exit 1
fi

timestamp() {
    date '+%Y-%m-%d %H:%M:%S'
}

log() {
    echo "$(timestamp) $1" | tee -a "${LOG_FILE}" >/dev/null
}

map_status() {
    case "$1" in
    running)
        echo "running"
        ;;
    exited | dead)
        echo "stopped"
        ;;
    restarting | created)
        echo "deploying"
        ;;
    *)
        echo "failed"
        ;;
    esac
}

containers=$(docker ps -a --format '{{.Names}}|{{.State}}')

if [[ -z "${containers}" ]]; then
    log "No containers discovered."
    exit 0
fi

for entry in ${containers}; do
    container="${entry%%|*}"
    docker_state="${entry##*|}"
    status=$(map_status "${docker_state}")
    uptime=$(docker inspect -f '{{.State.StartedAt}}' "${container}" 2>/dev/null || echo "unknown")

    response=$(curl -sS -X POST "${API_URL}" \
        -H "X-Monitor-Token: ${API_TOKEN}" \
        --max-time 15 \
        -d "container=${container}" \
        -d "status=${status}" \
        -d "uptime=${uptime}")

    log "[${container}] docker=${docker_state} mapped=${status} api_response=${response}"
done
