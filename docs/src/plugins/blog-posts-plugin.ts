import type {LoadContext, Plugin} from '@docusaurus/types';
import * as fs from 'fs';
import * as path from 'path';
import matter from 'gray-matter';

type BlogPostMeta = {
  slug: string;
  title: string;
  description: string;
  date: string;
  tags: string[];
  authors: string[];
};

export default function blogPostsPlugin(context: LoadContext): Plugin {
  return {
    name: 'blog-posts-plugin',

    async contentLoaded({actions}) {
      const {setGlobalData} = actions;
      const blogDir = path.join(context.siteDir, 'blog');

      const posts: BlogPostMeta[] = [];

      if (fs.existsSync(blogDir)) {
        const files = fs.readdirSync(blogDir).filter(f => f.endsWith('.md'));

        for (const file of files) {
          const filePath = path.join(blogDir, file);
          const content = fs.readFileSync(filePath, 'utf-8');
          const {data, content: body} = matter(content);

          if (data.slug && data.title) {
            // Extract description from truncated content or first paragraph
            let description = '';
            const truncateIndex = body.indexOf('<!-- truncate -->');
            if (truncateIndex !== -1) {
              description = body.substring(0, truncateIndex).trim();
            } else {
              // Get first paragraph
              const firstPara = body.split('\n\n')[0];
              description = firstPara?.replace(/^#.*\n/, '').trim() || '';
            }

            // Clean markdown from description
            description = description
              .replace(/^#+\s+.*$/gm, '') // Remove headers
              .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1') // Convert links to text
              .replace(/[*_`]/g, '') // Remove formatting
              .replace(/\n+/g, ' ') // Collapse newlines
              .trim()
              .substring(0, 200);

            if (description.length === 200) {
              description += '...';
            }

            // Parse date from filename (YYYY-MM-DD-slug.md)
            const dateMatch = file.match(/^(\d{4}-\d{2}-\d{2})/);
            const date = dateMatch ? dateMatch[1] : data.date || '';

            posts.push({
              slug: data.slug,
              title: data.title,
              description,
              date,
              tags: data.tags || [],
              authors: data.authors || [],
            });
          }
        }

        // Sort by date descending
        posts.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
      }

      setGlobalData({posts});
    },
  };
}
