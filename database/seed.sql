-- Sobur portfolio — seed data
-- Import after schema.sql: mysql -u root -p sobur_portfolio < database/seed.sql
--
-- TODO: replace the live_url values below with the real project URLs.

USE `sobur_portfolio`;

INSERT INTO `projects`
    (`slug`, `title`, `category`, `tagline`, `description`, `tags`,
     `brand_primary`, `brand_secondary`, `brand_surface`, `live_url`, `mockup_type`, `is_featured`, `sort_order`)
VALUES
    (
        'isobash',
        'ISOBASH',
        'AI workspace',
        'Your AI. Your Agents. Your Workspace.',
        'One platform for AI chat, agents, deep research, and image and video generation.',
        'Design,Full-stack,Next.js,NestJS,PostgreSQL',
        '#1E6FE8', '#FFFFFF', '#EAF3FF',
        'https://example.com/isobash',   -- TODO: real live URL
        'isobash', 1, 1
    ),
    (
        'abagaf',
        'Abagaf',
        'Ecommerce website',
        'A storefront that sells.',
        'Rebuilt a slow, hard-to-manage shop into a fast storefront with a one-page checkout that lifted mobile conversions.',
        'Design,Development,Laravel,MySQL,Stripe',
        '#1E6FE8', '#FF7A1A', '#FFFFFF',
        'https://example.com/abagaf',    -- TODO: real live URL
        'abagaf', 0, 2
    ),
    (
        'sobur-prime-estates',
        'Sobur Prime Estates',
        'Real estate website',
        'Find home, faster.',
        'Turned scattered listings and manual enquiries into a searchable property platform with map search and lead capture.',
        'Design,Development,PHP,MySQL,Google Maps API',
        '#0A7F52', '#FFFFFF', '#E3F4EA',
        'https://example.com/prime-estates',  -- TODO: real live URL
        'prime-estates', 0, 3
    );
