#!/usr/bin/env python3
"""Convert an agent Markdown file (YAML-ish frontmatter + body) into the
JSON object that `claude --agents` expects:

    {"<name>": {"description": ..., "prompt": ..., "model": ..., "tools": [...]}}

Usage: agents_json.py agents/docs-lookup.md
"""
import json
import sys
from pathlib import Path


def parse(path: Path) -> dict:
    text = path.read_text(encoding="utf-8")
    if not text.startswith("---\n"):
        sys.exit(f"{path}: missing frontmatter")
    _, front, body = text.split("---\n", 2)

    meta = {}
    for line in front.splitlines():
        if ":" in line:
            key, value = line.split(":", 1)
            meta[key.strip()] = value.strip()

    name = meta.pop("name", path.stem)
    agent = {"description": meta["description"], "prompt": body.strip()}
    if meta.get("model"):
        agent["model"] = meta["model"]
    if meta.get("tools"):
        agent["tools"] = [t.strip() for t in meta["tools"].split(",") if t.strip()]
    return {name: agent}


if __name__ == "__main__":
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    print(json.dumps(parse(Path(sys.argv[1]))))
