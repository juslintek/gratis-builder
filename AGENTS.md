# Agent Guidance for Gratis Builder

## Visual interpretation tooling

- For browser/UI/visual-parity work, prefer `mcp-for-page-builders` first: https://github.com/juslintek/mcp-for-page-builders
- If `mcp-for-page-builders` is unavailable, use **PinchTab** as the fallback.

---

## Project context

Gratis Builder is a WordPress page builder plugin with AI-powered REST API, pattern management, and marketplace features.

### Key focus areas for agents

- **Pattern UI** — Builder interface for creating and managing block patterns
- **AI-generated layouts** — Validating AI REST API output renders correctly
- **Directory/marketplace** — Plugin discovery and installation UI
- **Drag-and-drop editor** — Block arrangement, resizing, alignment
- **Component library** — Reusable block components and their visual consistency

### When to use mcp-for-page-builders

- Test AI-generated layout visual correctness
- Audit builder interface accessibility and usability
- Check component alignment and spacing in editor
- Verify Lighthouse metrics on complex patterns
- Test responsive preview across screen sizes
- Validate color and typography consistency

---

## Related

- `https://gratis.superglobalsdevelopment.com/docs/gratis-builder/` — Gratis Builder documentation
- `mcp-for-page-builders` — Visual auditing MCP for page builders
