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
   
   Note on search (Algolia): Local dev works without any Algolia configuration. If you want the search bar to appear locally, copy `.env.example` to `.env` in the `docs` directory and set:
   
   ```bash
   ALGOLIA_APP_ID=your_app_id
   ALGOLIA_API_KEY=your_search_only_api_key
   ```
   
   When not set, the search bar is hidden during local development so `npm run start` does not fail.
5. To build the static documentation site:
   ```bash
   npm run build
   ```
   The output will be in `docs/build/`.

For more details on contributing to the documentation, see the guidelines in `docs/README.md`.
