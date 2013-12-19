CREATE TABLE IF NOT EXISTS `Meeting_Meetings` (
  `id` INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(256) NOT NULL
) ENGINE InnoDB COLLATE 'utf8_unicode_ci';

CREATE TABLE IF NOT EXISTS `Meeting_MeetingsDetails` (
  `id` INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `meeting_id` INTEGER NOT NULL,
  `key` VARCHAR(256) NOT NULL,
  `value` TEXT NOT NULL
) ENGINE InnoDB COLLATE 'utf8_unicode_ci';

CREATE TABLE IF NOT EXISTS `Meeting_Participants` (
  `id` INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(256) NOT NULL
) ENGINE InnoDB COLLATE 'utf8_unicode_ci';

CREATE TABLE IF NOT EXISTS `Meeting_ParticipantsDetails` (
  `id` INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `participant_id` INTEGER NOT NULL,
  `key` VARCHAR(256) NOT NULL,
  `value` TEXT NOT NULL
) ENGINE InnoDB COLLATE 'utf8_unicode_ci';

