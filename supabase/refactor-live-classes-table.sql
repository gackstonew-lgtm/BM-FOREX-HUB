-- supabase/refactor-live-classes-table.sql
-- Comprehensive Supabase schema update for Live Classes module

CREATE TABLE IF NOT EXISTS public.live_classes (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    instructor_name TEXT NOT NULL,
    instructor_photo TEXT,
    meeting_platform TEXT DEFAULT 'Google Meet',
    meeting_link TEXT NOT NULL,
    meeting_id TEXT,
    meeting_password TEXT,
    start_datetime TIMESTAMPTZ NOT NULL,
    end_datetime TIMESTAMPTZ,
    timezone TEXT DEFAULT 'Africa/Nairobi (EAT)',
    duration_minutes INTEGER DEFAULT 60 NOT NULL,
    class_banner TEXT,
    class_notes_pdf TEXT,
    recording_link TEXT,
    max_attendees INTEGER DEFAULT 500,
    visibility TEXT DEFAULT 'published',
    live_status TEXT DEFAULT 'scheduled',
    meeting_status TEXT DEFAULT 'waiting',
    class_status TEXT DEFAULT 'active',
    is_live BOOLEAN DEFAULT FALSE NOT NULL,
    meeting_ready BOOLEAN DEFAULT FALSE NOT NULL,
    created_by TEXT DEFAULT 'Admin',
    created_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- Enable RLS
ALTER TABLE public.live_classes ENABLE ROW LEVEL SECURITY;

-- Allow read access to all users
DROP POLICY IF EXISTS "Allow read access to all users" ON public.live_classes;
CREATE POLICY "Allow read access to all users" 
ON public.live_classes 
FOR SELECT 
USING (true);

-- Allow full access to service_role (admins)
DROP POLICY IF EXISTS "Allow full access to service_role" ON public.live_classes;
CREATE POLICY "Allow full access to service_role" 
ON public.live_classes 
FOR ALL 
TO service_role 
USING (true) 
WITH CHECK (true);
