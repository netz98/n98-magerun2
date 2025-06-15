---
title: Create GitHub Pull Requests
description: How to create a pull request on GitHub
---

Creating a pull request (PR) allows you to propose changes to the n98-magerun2 project. Follow these steps to ensure your contribution is reviewed and merged smoothly.

### 1. Fork the Repository

- Go to the [n98-magerun2 GitHub repository](https://github.com/netz98/n98-magerun2).
- Click the **Fork** button in the top right to create your own copy of the repository.

### 2. Clone Your Fork

Clone your forked repository to your local machine:

```bash
git clone https://github.com/<your-username>/n98-magerun2.git
cd n98-magerun2
```

### 3. Create a New Branch

Always create a new branch for your changes:

```bash
git switch -c my-feature-branch
```

### 4. Make Your Changes

- Implement your feature, bugfix, or documentation update.
- Make sure to add or update tests as needed.
- Update documentation in the `docs/` directory if your change affects usage or features.

### 5. Commit and Push

Commit your changes with a descriptive message:

```bash
git add .
git commit -m "Describe your change"
git push origin my-feature-branch
```

Use Conventional Commits for your commit messages, e.g., `feat: add new command`, `fix: correct typo in documentation`.

### 6. Open a Pull Request

- Go to your fork on GitHub.
- Click the **Compare & pull request** button next to your branch.
- Fill out the pull request template:
  - Provide a clear summary of your changes.
  - Reference any related issues (e.g., `Fixes #123`).
- Submit the pull request against the `develop` branch (unless otherwise instructed).

:::tip
If you push your changes, then a link to create a pull request will appear automatically in your terminal, which you can click (in a modern terminal) to open the PR page.
:::

### 7. Respond to Feedback

- Project maintainers may review your PR and request changes or clarifications.
- Make any necessary updates and push them to your branch; the PR will update automatically.

### 8. PR Approval and Merge

- Once approved, a maintainer will merge your PR.
- Your contribution will become part of the project!

---

**Tip:** For more details, see the [Contribution Guide](https://netz98.github.io/n98-magerun2/contributing/).
