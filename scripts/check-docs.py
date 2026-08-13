from __future__ import annotations

import re
import sys
from pathlib import Path
from urllib.parse import unquote

ROOT = Path(__file__).resolve().parents[1]
REQUIRED = [
    "README.md",
    "LICENSE",
    "CHANGELOG.md",
    "docs/PRODUCT.md",
    "docs/ARCHITECTURE.md",
    "docs/DATA_MODEL.md",
    "docs/API_MAP.md",
    "docs/SECURITY_MODEL.md",
    "docs/DECISIONS.md",
    "docs/DEFINITION_OF_DONE.md",
    "docs/ROADMAP.md",
]
LINK_RE = re.compile(r"\[[^\]]+\]\(([^)]+)\)")

errors: list[str] = []

for relative in REQUIRED:
    if not (ROOT / relative).is_file():
        errors.append(f"missing required file: {relative}")

for markdown in ROOT.rglob("*.md"):
    text = markdown.read_text(encoding="utf-8")
    for raw_target in LINK_RE.findall(text):
        target = raw_target.strip().split("#", 1)[0]
        if not target or "://" in target or target.startswith("mailto:"):
            continue
        decoded = unquote(target)
        resolved = (markdown.parent / decoded).resolve()
        try:
            resolved.relative_to(ROOT)
        except ValueError:
            errors.append(f"{markdown.relative_to(ROOT)}: link escapes repository: {raw_target}")
            continue
        if not resolved.exists():
            errors.append(f"{markdown.relative_to(ROOT)}: broken local link: {raw_target}")

if errors:
    print("DOCS_QUALITY=FAIL")
    for error in errors:
        print(f"- {error}")
    sys.exit(1)

print("DOCS_QUALITY=PASS")
