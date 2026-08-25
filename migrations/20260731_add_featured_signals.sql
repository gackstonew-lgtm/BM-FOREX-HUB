-- Featured Signals table for Market Overview
CREATE TABLE IF NOT EXISTS featured_signals (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  pair VARCHAR(20) NOT NULL,
  direction VARCHAR(10) NOT NULL CHECK (direction IN ('buy','sell')),
  entry_price VARCHAR(30) NOT NULL,
  take_profit VARCHAR(30) NOT NULL,
  stop_loss VARCHAR(30) NOT NULL,
  risk VARCHAR(100) DEFAULT '',
  confidence INTEGER DEFAULT 75 CHECK (confidence >= 0 AND confidence <= 100),
  status VARCHAR(20) DEFAULT 'published' CHECK (status IN ('published','draft')),
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- RLS
ALTER TABLE featured_signals ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Public can read published signals"
  ON featured_signals FOR SELECT
  USING (status = 'published');

CREATE POLICY "Admins can manage signals"
  ON featured_signals FOR ALL
  USING (auth.role() = 'service_role');
