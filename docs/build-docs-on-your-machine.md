## Official Documentation

The `docs` directory contains the official documentation for n98-magerun2. We use [Docusaurus](https://docusaurus.io/) to generate and manage the documentation site.

### Setup and Building Documentation

1. Enter the development container (if using ddev):
   ```bash
   ddev ssh
   ```
2. Change to the docs directory:
   ```bash
   cd docs
   ```
3. Install dependencies:
   ```bash
   npm install
   ```
4. To preview the documentation locally:
   ```bash
   npm run start
   ```
   This will start a local server (usually at http://localhost:3000) for live preview.
5. To build the static documentation site:
   ```bash
   npm run build
   ```
   The output will be in `docs/build/`.

For more details on contributing to the documentation, see the guidelines in `docs/README.md`.
