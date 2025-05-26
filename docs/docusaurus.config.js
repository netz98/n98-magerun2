// @ts-check
// `@type` JSDoc annotations allow editor autocompletion and type checking
// (when paired with `@ts-check`).
// There are various equivalent ways to declare your Docusaurus config.
// See: https://docusaurus.io/docs/api/docusaurus-config

import {themes as prismThemes} from 'prism-react-renderer';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'n98-magerun2',
  tagline: 'The CLI Swiss Army Knife for Magento 2',
  favicon: 'img/favicon.ico',

  // Set the production url of your site here
  url: 'https://netz98.github.io',
  // Set the /<baseUrl>/ pathname under which your site is served
  // For GitHub pages deployment, it is often '/<projectName>/'
  baseUrl: '/n98-magerun2/', // Corrected baseUrl

  // GitHub pages deployment config.
  organizationName: 'netz98', // Corrected organizationName
  projectName: 'n98-magerun2', // Corrected projectName

  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',

  // Even if you don't use internationalization, you can use this field to set
  // useful metadata like html lang. For example, if your site is Chinese, you
  // may want to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: './sidebars.js',
          routeBasePath: '/',
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/netz98/n98-magerun2/tree/main/docs/', // Corrected editUrl
        },
        blog: false, // Disabled the blog
        theme: {
          customCss: './src/css/custom.css',
        },
      }),
    ],
  ],

  themeConfig:
  /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      // Replace with your project's social card
      image: 'img/docusaurus-social-card.jpg', // Consider creating a specific social card for n98-magerun2
      navbar: {
        title: 'n98-magerun2',
        logo: {
          alt: 'n98-magerun2 Logo',
          src: 'img/logo.svg', // Ensure this path is correct relative to the static folder
        },
        items: [
          {
            type: 'docSidebar',
            sidebarId: 'tutorialSidebar', // Ensure this sidebarId matches the one in sidebars.js
            position: 'left',
            label: 'Docs',
          },
          {
            href: 'https://github.com/netz98/n98-magerun2', // Corrected GitHub link
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              {
                label: 'Introduction', // Changed label from Tutorial for clarity
                to: '/docs/intro', // This will link to /n98-magerun2/docs/intro
              },
            ],
          },
          {
            title: 'Community',
            items: [
              // Consider updating these to n98-magerun2 specific community links if available
              {
                label: 'Stack Overflow (Magento)',
                href: 'https://magento.stackexchange.com/questions/tagged/n98-magerun2',
              },
              {
                label: 'Report an Issue',
                href: 'https://github.com/netz98/n98-magerun2/issues',
              },
              // {
              //   label: 'Discord',
              //   href: 'https://discordapp.com/invite/docusaurus', // Example, update if you have one
              // },
              // {
              //   label: 'X',
              //   href: 'https://x.com/docusaurus', // Example, update if you have one
              // },
            ],
          },
          {
            title: 'More',
            items: [
              {
                label: 'GitHub',
                href: 'https://github.com/netz98/n98-magerun2', // Corrected GitHub link
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} The n98-magerun2 Maintainers. Built with Docusaurus.`, // Corrected copyright
      },
      prism: {
        theme: prismThemes.github,
        darkTheme: prismThemes.dracula,
      },
    }),
};

export default config;
