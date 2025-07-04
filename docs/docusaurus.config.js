// @ts-check
// Note: type annotations allow type checking and IDEs autocompletion

import { themes as prismThemes } from 'prism-react-renderer'
import { configDotenv } from 'dotenv'

configDotenv()

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'n98-magerun2',
  tagline: 'Magento 2 Console Tool',
  url: 'https://netz98.github.io',
  baseUrl: '/n98-magerun2/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',
  organizationName: 'netz98',
  projectName: 'n98-magerun2',
  future: {
    experimental_faster: true,
    v4: true
  },
  markdown: {
    mermaid: true
  },
  customFields: {
    meilisearchUrl: process.env.MEILISEARCH_URL || '',
    meilisearchApiKey: process.env.MEILISEARCH_API_KEY || '',
    meilisearchIndexUid: process.env.MEILISEARCH_INDEX_UID || 'n98-magerun2-docs',
  },
  themes: ['@docusaurus/theme-mermaid'],
  themeConfig: {
    navbar: {
      logo: {
        alt: 'n98-magerun2 Logo',
        src: 'img/logo.svg',
        height: 64
      },
      items: [
        {
          type: 'doc',
          position: 'left',
          docId: 'intro',
          label: 'Docs'
        },
        {
          type: 'doc',
          position: 'left',
          docId: 'command-docs/index',
          label: 'Commands'
        },
        {
          label: 'GitHub',
          href: 'https://github.com/netz98/n98-magerun2'
        },
        {
          label: 'valantic (formerly netz98)',
          href: 'https://netz98.de'
        }
      ]
    },
    mermaid: {
      theme: { light: 'neutral', dark: 'forest' }
    },
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.vsDark,
      additionalLanguages: ['php', 'bash', 'sql']
    }
  },
  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */ ({
        docs: {
          path: 'docs',
          routeBasePath: '/',
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl:
            'https://github.com/netz98/n98-magerun2/edit/develop/docs/'
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css')
        }
      })
    ]
  ]
}

module.exports = config
