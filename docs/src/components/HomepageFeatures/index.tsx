import type {ReactNode} from 'react';
import clsx from 'clsx';
import Link from '@docusaurus/Link';
import Heading from '@theme/Heading';
import {usePluginData} from '@docusaurus/useGlobalData';
import styles from './styles.module.css';

type BlogPost = {
  slug: string;
  title: string;
  description: string;
  date: string;
  tags: string[];
};

type BlogPostsData = {
  posts: BlogPost[];
};

function BlogPostCard({post}: {post: BlogPost}) {
  const date = new Date(post.date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });

  return (
    <article className={clsx('col col--4', styles.blogCard)}>
      <div className={styles.cardContent}>
        <div className={styles.cardMeta}>
          <time dateTime={post.date}>{date}</time>
        </div>
        <Heading as="h3" className={styles.cardTitle}>
          <Link to={`/blog/${post.slug}`}>{post.title}</Link>
        </Heading>
        <p className={styles.cardDescription}>{post.description}</p>
        {post.tags.length > 0 && (
          <div className={styles.cardTags}>
            {post.tags.slice(0, 3).map((tag) => (
              <span key={tag} className={styles.tag}>
                {tag}
              </span>
            ))}
          </div>
        )}
        <Link to={`/blog/${post.slug}`} className={styles.readMore}>
          Read more →
        </Link>
      </div>
    </article>
  );
}

export default function HomepageFeatures(): ReactNode {
  const data = usePluginData('blog-posts-plugin') as BlogPostsData | undefined;
  const posts = data?.posts?.slice(0, 3) || [];

  if (posts.length === 0) {
    return null;
  }

  return (
    <section className={styles.features}>
      <div className="container">
        <Heading as="h2" className={styles.sectionTitle}>
          Latest from the Blog
        </Heading>
        <div className="row">
          {posts.map((post) => (
            <BlogPostCard key={post.slug} post={post} />
          ))}
        </div>
        <div className={styles.viewAll}>
          <Link to="/blog" className="button button--outline button--primary">
            View all posts
          </Link>
        </div>
      </div>
    </section>
  );
}
