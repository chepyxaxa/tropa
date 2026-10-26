
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS excursions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS payment_methods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS application_statuses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    excursion_id INTEGER NOT NULL,
    excursion_date TEXT NOT NULL,
    participants INTEGER NOT NULL DEFAULT 1
        CHECK (participants > 0),
    payment_method_id INTEGER NOT NULL,
    status_id INTEGER NOT NULL,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (excursion_id) REFERENCES excursions(id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id),
    FOREIGN KEY (status_id) REFERENCES application_statuses(id)
);

CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    application_id INTEGER NOT NULL UNIQUE,
    review_text TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (application_id) REFERENCES applications(id)
);


INSERT OR IGNORE INTO excursions (name, description)
VALUES
    ('Обзорная по городу', 'Знакомство с основными достопримечательностями города.'),
    ('Музейный квартал', 'Посещение музеев и культурных мест.'),
    ('Загородный эко-маршрут', 'Прогулка по природным местам за городом.');

INSERT OR IGNORE INTO payment_methods (name)
VALUES
    ('Наличными'),
    ('Переводом по номеру телефона');

INSERT OR IGNORE INTO application_statuses (name)
VALUES
    ('Новая'),
    ('Подтверждена'),
    ('Экскурсия состоялась'),
    ('Отменена');