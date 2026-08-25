-- supabase/refactor-cms-modules.sql
-- Comprehensive Supabase schema update for Market Overview, Articles, Videos, Promotions, Signals, and Announcements

-- 1. Market Overview
CREATE TABLE IF NOT EXISTS public.market_overview (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    content TEXT,
    banner_url TEXT,
    button_label TEXT,
    button_url TEXT,
    priority INTEGER DEFAULT 0,
    status TEXT DEFAULT 'published',
    created_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL
);
ALTER TABLE public.market_overview ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Allow read to all" ON public.market_overview;
CREATE POLICY "Allow read to all" ON public.market_overview FOR SELECT USING (true);
DROP POLICY IF EXISTS "Allow service_role full" ON public.market_overview;
CREATE POLICY "Allow service_role full" ON public.market_overview FOR ALL TO service_role USING (true) WITH CHECK (true);

-- 2. Articles
CREATE TABLE IF NOT EXISTS public.articles (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    slug TEXT,
    category TEXT DEFAULT 'General',
    summary TEXT,
    content TEXT,
    cover_image TEXT,
    author_name TEXT DEFAULT 'BM Forex Hub Team',
    author_photo TEXT,
    read_time_minutes INTEGER DEFAULT 5,
    status TEXT DEFAULT 'published',
    created_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL
);
ALTER TABLE public.articles ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Allow read to all" ON public.articles;
CREATE POLICY "Allow read to all" ON public.articles FOR SELECT USING (true);
DROP POLICY IF EXISTS "Allow service_role full" ON public.articles;
CREATE POLICY "Allow service_role full" ON public.articles FOR ALL TO service_role USING (true) WITH CHECK (true);

-- 3. Videos
CREATE TABLE IF NOT EXISTS public.videos (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    video_url TEXT NOT NULL,
    embed_url TEXT,
    thumbnail_url TEXT,
    duration TEXT DEFAULT '10:00',
    category TEXT DEFAULT 'Strategy',
    status TEXT DEFAULT 'published',
    created_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL
);
ALTER TABLE public.videos ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Allow read to all" ON public.videos;
CREATE POLICY "Allow read to all" ON public.videos FOR SELECT USING (true);
DROP POLICY IF EXISTS "Allow service_role full" ON public.videos;
CREATE POLICY "Allow service_role full" ON public.videos FOR ALL TO service_role USING (true) WITH CHECK (true);

-- 4. Promotions
CREATE TABLE IF NOT EXISTS public.promotions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    banner_url TEXT,
    button_text TEXT DEFAULT 'Claim Now',
    button_link TEXT,
    start_date DATE,
    end_date DATE,
    discount_percentage INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL
);
ALTER TABLE public.promotions ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Allow read to all" ON public.promotions;
CREATE POLICY "Allow read to all" ON public.promotions FOR SELECT USING (true);
DROP POLICY IF EXISTS "Allow service_role full" ON public.promotions;
CREATE POLICY "Allow service_role full" ON public.promotions FOR ALL TO service_role USING (true) WITH CHECK (true);

-- 5. Featured Signals
CREATE TABLE IF NOT EXISTS public.featured_signals (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    pair TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'BUY',
    entry_price TEXT NOT NULL,
    stop_loss TEXT NOT NULL,
    take_profit_1 TEXT NOT NULL,
    take_profit_2 TEXT,
    confidence_level INTEGER DEFAULT 90,
    notes TEXT,
    status TEXT DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL
);
ALTER TABLE public.featured_signals ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Allow read to all" ON public.featured_signals;
CREATE POLICY "Allow read to all" ON public.featured_signals FOR SELECT USING (true);
DROP POLICY IF EXISTS "Allow service_role full" ON public.featured_signals;
CREATE POLICY "Allow service_role full" ON public.featured_signals FOR ALL TO service_role USING (true) WITH CHECK (true);

-- 6. Announcements
CREATE TABLE IF NOT EXISTS public.announcements (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    category TEXT DEFAULT 'General',
    target_audience TEXT DEFAULT 'All Users',
    status TEXT DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL
);
ALTER TABLE public.announcements ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Allow read to all" ON public.announcements;
CREATE POLICY "Allow read to all" ON public.announcements FOR SELECT USING (true);
DROP POLICY IF EXISTS "Allow service_role full" ON public.announcements;
CREATE POLICY "Allow service_role full" ON public.announcements FOR ALL TO service_role USING (true) WITH CHECK (true);
