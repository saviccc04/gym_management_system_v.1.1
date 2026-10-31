CREATE DATABASE IF NOT EXISTS gym_management_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gym_management_system;

CREATE TABLE admins (
    admin_id   INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE training_plans (
    plan_id    INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE trainers (
    trainer_id   INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(100) NOT NULL,
    last_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE members (
    member_id       INT AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    phone_number    VARCHAR(20),
    plan_id         INT,
    trainer_id      INT NULL,
    photo_path      VARCHAR(255),
    access_card_pdf VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_members_plan
        FOREIGN KEY (plan_id) REFERENCES training_plans (plan_id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_members_trainer
        FOREIGN KEY (trainer_id) REFERENCES trainers (trainer_id)
        ON DELETE SET NULL
);