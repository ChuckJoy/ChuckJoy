-- Update presets table to include image_path
USE tunetolight;

-- Add image_path column if it doesn't exist
-- Note: MySQL doesn't support IF NOT EXISTS in ALTER TABLE directly in all versions, 
-- but this is safe to run if the column doesn't exist. If it exists, it might error, which we can ignore.
ALTER TABLE presets ADD COLUMN image_path VARCHAR(255) DEFAULT NULL;
