-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 24 fév. 2026 à 12:43
-- Version du serveur : 10.4.21-MariaDB
-- Version de PHP : 7.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `social_automator`
--

-- --------------------------------------------------------

--
-- Structure de la table `billing_history`
--

CREATE TABLE `billing_history` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_invoice_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'usd',
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_pdf` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `content_series`
--

CREATE TABLE `content_series` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `niche` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `platforms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `content_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `schedule_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL ,
  --  `content_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_object('voice_id','adam','voice_speed',1.0,'image_style','cinematic','caption_style','tiktok-bold','video_length_seconds',30,'background_music','none','watermark',1) CHECK (json_valid(`content_config`)),
  -- `platforms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_object('youtube',json_object('enabled',0,'upload_style','public'),'tiktok',json_object('enabled',0,'allow_duet',1),'instagram',json_object('enabled',0,'share_to_feed',1)) CHECK (json_valid(`platforms`)),
  -- `content_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_object('default_hashtags',json_array(),'call_to_action','Follow for more!','tone','motivational','language','en') CHECK (json_valid(`content_rules`)),
  -- `schedule_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_object('days',json_array('monday','wednesday','friday'),'time','08:00','timezone','UTC') CHECK (json_valid(`schedule_config`)),
  
  `status` enum('active','paused','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `total_posts` int(11) DEFAULT 0,
  `last_post_at` datetime DEFAULT NULL,
  `next_post_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `error_logs`
--

CREATE TABLE `error_logs` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stack_trace` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` varchar(37) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `series_id` varchar(37) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_id` varchar(37) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `resolved` tinyint(1) DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `post_results`
--

CREATE TABLE `post_results` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `video_id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` enum('youtube','tiktok','instagram','linkedin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform_post_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_post_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','success','failed','partially_success') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `likes` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `shares` int(11) DEFAULT 0,
  `saves` int(11) DEFAULT 0,
  `posted_at` datetime DEFAULT NULL,
  `analytics_updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tier` enum('basic','standard','premium','agency') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_monthly_usd` decimal(10,2) DEFAULT NULL,
  `price_yearly_usd` decimal(10,2) DEFAULT NULL,
  `max_series` int(11) NOT NULL,
  `videos_per_series` int(11) NOT NULL,
  `max_duration_seconds` int(11) DEFAULT 60,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `usage_stats`
--

CREATE TABLE `usage_stats` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `videos_generated` int(11) DEFAULT 0,
  `videos_successful` int(11) DEFAULT 0,
  `videos_failed` int(11) DEFAULT 0,
  `openai_calls` int(11) DEFAULT 0,
  `elevenlabs_calls` int(11) DEFAULT 0,
  `replicate_calls` int(11) DEFAULT 0,
  `shotstack_calls` int(11) DEFAULT 0,
  `composio_calls` int(11) DEFAULT 0,
  `storage_bytes` bigint(20) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_tier` enum('free','basic','standard','premium') COLLATE utf8mb4_unicode_ci DEFAULT 'free',
  `subscription_status` enum('active','canceled','past_due','trialing') COLLATE utf8mb4_unicode_ci DEFAULT 'trialing',
  `stripe_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_ends_at` datetime DEFAULT NULL,
  `videos_generated_this_month` int(11) DEFAULT 0,
  `total_videos_generated` int(11) DEFAULT 0,
  `last_video_generated_at` datetime DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `email_notifications` tinyint(1) DEFAULT 1,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_social_accounts`
--

CREATE TABLE `user_social_accounts` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` enum('youtube','tiktok','instagram','linkedin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_channel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_token` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refresh_token` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `avatar_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `follower_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `videos`
--

CREATE TABLE `videos` (
  `id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `series_id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  `script` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`script`)),
  `hashtags_used` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audio_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images_used`)),
  `duration_seconds` int(11) DEFAULT NULL,
  `file_size_bytes` bigint(20) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `queued_at` datetime DEFAULT NULL,
  `processing_started_at` datetime DEFAULT NULL,
  `processing_completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add this to your database
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    `id` VARCHAR(37) NOT NULL PRIMARY KEY,
    `user_id` VARCHAR(37) NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `code` VARCHAR(6) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (`token`),
    INDEX idx_code (`code`),
    INDEX idx_expires (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `billing_history`
--
ALTER TABLE `billing_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_invoice_id` (`stripe_invoice_id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_stripe` (`stripe_invoice_id`,`stripe_subscription_id`);

--
-- Index pour la table `content_series`
--
ALTER TABLE `content_series`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`,`status`),
  ADD KEY `idx_next_post` (`next_post_at`,`status`),
  ADD KEY `idx_status_updated` (`status`,`updated_at`);
ALTER TABLE `content_series` ADD FULLTEXT KEY `idx_niche_search` (`niche`,`name`);

--
-- Index pour la table `error_logs`
--
ALTER TABLE `error_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_created` (`error_type`,`created_at`),
  ADD KEY `idx_unresolved` (`resolved`,`created_at`),
  ADD KEY `idx_related` (`user_id`,`series_id`,`video_id`);

--
-- Index pour la table `post_results`
--
ALTER TABLE `post_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_video_platform` (`video_id`,`platform`),
  ADD KEY `idx_platform_status` (`platform`,`status`),
  ADD KEY `idx_posted` (`posted_at`),
  ADD KEY `idx_analytics_needed` (`analytics_updated_at`);

--
-- Index pour la table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_price_id` (`stripe_price_id`),
  ADD KEY `idx_tier_active` (`tier`,`is_active`);

--
-- Index pour la table `usage_stats`
--
ALTER TABLE `usage_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_date` (`user_id`,`date`),
  ADD KEY `idx_date` (`date`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_subscription` (`subscription_tier`,`subscription_status`),
  ADD KEY `idx_stripe` (`stripe_customer_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Index pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD UNIQUE KEY `refresh_token` (`refresh_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_tokens` (`session_token`,`refresh_token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Index pour la table `user_social_accounts`
--
ALTER TABLE `user_social_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_platform` (`user_id`,`platform`),
  ADD KEY `idx_token_expiry` (`token_expires_at`),
  ADD KEY `idx_platform` (`platform`,`platform_user_id`);

--
-- Index pour la table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`,`created_at`),
  ADD KEY `idx_series` (`series_id`,`status`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_cleanup` (`status`,`created_at`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `billing_history`
--
ALTER TABLE `billing_history`
  ADD CONSTRAINT `billing_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `content_series`
--
ALTER TABLE `content_series`
  ADD CONSTRAINT `content_series_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `post_results`
--
ALTER TABLE `post_results`
  ADD CONSTRAINT `post_results_ibfk_1` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `usage_stats`
--
ALTER TABLE `usage_stats`
  ADD CONSTRAINT `usage_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_social_accounts`
--
ALTER TABLE `user_social_accounts`
  ADD CONSTRAINT `user_social_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `password_reset_tokens` ADD FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,

--
-- Contraintes pour la table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `content_series` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `videos_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
