ALTER TABLE course
ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'draft',
ADD COLUMN moderation_comment TEXT; 