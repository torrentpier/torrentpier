import type {ReactNode} from 'react';
import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

type FeatureItem = {
  title: string;
  Svg: React.ComponentType<React.ComponentProps<'svg'>>;
  description: ReactNode;
};

const FeatureList: FeatureItem[] = [
  {
    title: 'Modern Architecture',
    Svg: require('@site/static/img/logo.svg').default,
    description: (
      <>
        Built with Laravel 12 and React 19, TorrentPier leverages the latest
        web technologies for performance, security, and developer experience.
      </>
    ),
  },
  {
    title: 'Feature Rich',
    Svg: require('@site/static/img/logo.svg').default,
    description: (
      <>
        Complete BitTorrent tracker with user management, forums, statistics,
        anti-cheat protection, and extensive moderation tools out of the box.
      </>
    ),
  },
  {
    title: 'Easy to Customize',
    Svg: require('@site/static/img/logo.svg').default,
    description: (
      <>
        Clean codebase following Laravel best practices. Extend functionality
        with plugins, themes, and custom modifications easily.
      </>
    ),
  },
];

function Feature({title, Svg, description}: FeatureItem) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} role="img" />
      </div>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures(): ReactNode {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
