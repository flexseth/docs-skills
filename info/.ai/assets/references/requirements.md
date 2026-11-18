# Requirements

## GitHub CLI (`gh`)

The `gh` CLI is required for agents to interact with GitHub Issues. Install it before using any agents in this package.

### Installation GitHub CLI

**macOS (Homebrew):**

```bash
brew install gh
```

**Windows (winget):**

```bash
winget install --id GitHub.cli
```

**Linux (apt):**

```bash
sudo apt install gh
```

- Refer to the [official GitHub CLI documentation](https://cli.github.com/).

### Authentication

After installing, authenticate with your GitHub account:

```bash
gh auth login
```

Verify the installation:

```bash
gh --version
```

## Claude Code

Claude Code must be used for AI processing within the agent pipeline. Ensure it is installed and properly configured before running any agents that rely on AI capabilities.

### Installation Claude Code

Follow the official installation instructions for Claude Code, which may vary depending on your operating system and environment.

### Authentication

Authenticate Claude Code as required by the official documentation to enable AI processing within the agent pipeline.