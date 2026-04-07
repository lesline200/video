-- Migration : Ajouter les colonnes title, description, scheduled_at, is_published à la table videos
-- Ces colonnes sont nécessaires pour la vue détail vidéo (édition titre/description, planification)

ALTER TABLE `videos`
  ADD COLUMN `title` VARCHAR(100) DEFAULT NULL AFTER `user_id`,
  ADD COLUMN `description` TEXT DEFAULT NULL AFTER `title`,
  ADD COLUMN `scheduled_at` DATETIME DEFAULT NULL AFTER `processing_completed_at`,
  ADD COLUMN `is_published` TINYINT(1) DEFAULT 0 AFTER `scheduled_at`;
