from __future__ import annotations

import json
import re
import sys
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[1]
SPEC_PATH = ROOT / "docs" / "openapi.json"
ROUTES_PATH = ROOT / "apps" / "api" / "routes" / "api.php"
DEADLINE_INPUT_PATH = ROOT / "apps" / "api" / "app" / "Opportunities" / "DeadlineInput.php"
DASHBOARD_TYPES_PATH = ROOT / "apps" / "web" / "src" / "lib" / "dashboard-api.ts"

ROUTE_RE = re.compile(r"Route::(get|post|patch|delete)\('([^']+)'")
AUTH_PREFIXED_PATHS = {"/register", "/login"}
PUBLIC_OPERATIONS = {
    ("GET", "/health/live"),
    ("POST", "/auth/register"),
    ("POST", "/auth/login"),
}
EXPECTED_ENUMS = {
    "OpportunityType": ["JOB", "INTERNSHIP", "SCHOLARSHIP", "PROGRAM", "OTHER"],
    "OpportunityStatus": [
        "SAVED", "PREPARING", "APPLIED", "INTERVIEWING", "OFFERED",
        "ACCEPTED", "REJECTED", "WITHDRAWN", "EXPIRED",
    ],
    "OpportunityPriority": ["LOW", "MEDIUM", "HIGH"],
    "DeadlinePrecision": ["DATE", "DATETIME"],
    "DeadlineAttention": ["OVERDUE", "DUE_SOON", "UPCOMING"],
    "OpportunityEventType": ["CREATED", "UPDATED", "STATUS_CHANGED", "ARCHIVED", "RESTORED"],
}
HTTP_METHODS = {"get", "post", "patch", "delete", "put"}


def finish(errors: list[str]) -> None:
    if not errors:
        return
    print("CONTRACT_QUALITY=FAIL")
    for error in errors:
        print(f"- {error}")
    sys.exit(1)


def collect_refs(value: Any) -> list[str]:
    refs: list[str] = []
    if isinstance(value, dict):
        for key, child in value.items():
            if key == "$ref" and isinstance(child, str):
                refs.append(child)
            else:
                refs.extend(collect_refs(child))
    elif isinstance(value, list):
        for child in value:
            refs.extend(collect_refs(child))
    return refs


def normalize_route(method: str, path: str) -> tuple[str, str]:
    normalized_path = f"/auth{path}" if path in AUTH_PREFIXED_PATHS else path
    return method.upper(), normalized_path


errors: list[str] = []
try:
    spec = json.loads(SPEC_PATH.read_text(encoding="utf-8"))
except (OSError, json.JSONDecodeError) as exc:
    print(f"CONTRACT_QUALITY=FAIL\n- cannot load docs/openapi.json: {exc}")
    sys.exit(1)

if spec.get("openapi") != "3.1.0":
    errors.append("OpenAPI version must be exactly 3.1.0")
if spec.get("info", {}).get("version") != "1.0.0":
    errors.append("OpenAPI info.version must be exactly 1.0.0")

route_text = ROUTES_PATH.read_text(encoding="utf-8")
implemented = {normalize_route(method, path) for method, path in ROUTE_RE.findall(route_text)}

documented: set[tuple[str, str]] = set()
operation_ids: set[str] = set()
for path, path_item in spec.get("paths", {}).items():
    if not isinstance(path_item, dict):
        errors.append(f"invalid path item: {path}")
        continue
    for method, operation in path_item.items():
        if method not in HTTP_METHODS:
            continue
        if not isinstance(operation, dict):
            errors.append(f"invalid operation: {method.upper()} {path}")
            continue
        pair = (method.upper(), path)
        documented.add(pair)
        operation_id = operation.get("operationId")
        if not isinstance(operation_id, str) or not operation_id:
            errors.append(f"missing operationId: {pair[0]} {path}")
        elif operation_id in operation_ids:
            errors.append(f"duplicate operationId: {operation_id}")
        else:
            operation_ids.add(operation_id)

        security = operation.get("security")
        if pair in PUBLIC_OPERATIONS:
            if security != []:
                errors.append(f"public operation must declare security: []: {pair[0]} {path}")
        elif security != [{"sessionCookie": []}]:
            errors.append(f"private operation must require sessionCookie: {pair[0]} {path}")

for pair in sorted(implemented - documented):
    errors.append(f"Laravel route missing from OpenAPI: {pair[0]} {pair[1]}")
for pair in sorted(documented - implemented):
    errors.append(f"OpenAPI operation missing from Laravel routes: {pair[0]} {pair[1]}")

components = spec.get("components", {})
schemas = components.get("schemas", {})
for name, expected in EXPECTED_ENUMS.items():
    actual = schemas.get(name, {}).get("enum")
    if actual != expected:
        errors.append(f"{name} enum drift: expected {expected}, got {actual}")

session_scheme = components.get("securitySchemes", {}).get("sessionCookie", {})
if session_scheme.get("type") != "apiKey" or session_scheme.get("in") != "cookie":
    errors.append("sessionCookie must be an apiKey security scheme in a cookie")

for ref in collect_refs(spec):
    schema_prefix = "#/components/schemas/"
    response_prefix = "#/components/responses/"
    parameter_prefix = "#/components/parameters/"
    if ref.startswith(schema_prefix):
        if ref.removeprefix(schema_prefix) not in schemas:
            errors.append(f"unresolved schema reference: {ref}")
    elif ref.startswith(response_prefix):
        if ref.removeprefix(response_prefix) not in components.get("responses", {}):
            errors.append(f"unresolved response reference: {ref}")
    elif ref.startswith(parameter_prefix):
        if ref.removeprefix(parameter_prefix) not in components.get("parameters", {}):
            errors.append(f"unresolved parameter reference: {ref}")
    elif ref.startswith("#/"):
        errors.append(f"unsupported local reference target: {ref}")

source_deadline = DEADLINE_INPUT_PATH.read_text(encoding="utf-8")
if "Rule::in(['DATE', 'DATETIME'])" not in source_deadline:
    errors.append("backend deadline precision source no longer exposes DATE/DATETIME")

ts_contract = DASHBOARD_TYPES_PATH.read_text(encoding="utf-8")
if 'deadline_precision: "DATE" | "DATETIME" | null;' not in ts_contract:
    errors.append("dashboard TypeScript contract is not aligned with DATE/DATETIME")
if 'deadline_precision: "DATE" | "EXACT" | null;' in ts_contract:
    errors.append("legacy EXACT dashboard deadline precision type is forbidden")

finish(errors)
print(f"CONTRACT_QUALITY=PASS OPERATIONS={len(documented)} SCHEMAS={len(schemas)}")
