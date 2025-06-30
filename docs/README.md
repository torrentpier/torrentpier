# TorrentPier Documentation

This directory contains the documentation for TorrentPier, built with [Docusaurus](https://docusaurus.io/).

## Local Development

```bash
npm install
npm start
```

This command starts a local development server and opens up a browser window. Most changes are reflected live without having to restart the server.

## Build

```bash
npm run build
```

This command generates static content into the `build` directory and can be served using any static contents hosting service.

## Deployment

The documentation is automatically deployed to GitHub Pages when changes are pushed to the main branch. The deployment is handled by GitHub Actions.

## Structure

- `/docs` - Main documentation content
- `/blog` - Development blog posts
- `/src` - Custom React components and pages
- `/static` - Static assets like images

## Contributing

1. Create or edit markdown files in the `/docs` directory
2. Add new pages to the sidebar by updating `sidebars.ts`
3. Test locally with `npm start`
4. Submit a pull request

## Writing Documentation

- Use clear, concise language
- Include code examples where relevant
- Add screenshots for UI features
- Keep the audience (developers) in mind
- Follow the existing structure and style

## Resources

- [Docusaurus Documentation](https://docusaurus.io/docs)
- [Markdown Guide](https://www.markdownguide.org/)
- [MDX Documentation](https://mdxjs.com/)
