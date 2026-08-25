-- Create live_classes table
CREATE TABLE IF NOT EXISTS public.live_classes (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    instructor TEXT NOT NULL,
    class_date DATE NOT NULL,
    class_time TEXT NOT NULL,
    duration_minutes INTEGER NOT NULL,
    description TEXT,
    meeting_link TEXT NOT NULL,
    is_live BOOLEAN DEFAULT FALSE NOT NULL,
    meeting_ready BOOLEAN DEFAULT FALSE NOT NULL,
    created_at TIMESTAMPTZ DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- Enable RLS
ALTER TABLE public.live_classes ENABLE ROW LEVEL SECURITY;

-- Policy: Allow read access to all (public & authenticated)
CREATE POLICY "Allow read access to all users" 
ON public.live_classes 
FOR SELECT 
USING (true);

-- Policy: Allow all access to service_role (admins)
CREATE POLICY "Allow full access to service_role" 
ON public.live_classes 
FOR ALL 
TO service_role 
USING (true) 
WITH CHECK (true);
