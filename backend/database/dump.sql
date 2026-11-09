
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
    description TEXT,
    price_per_person REAL NOT NULL DEFAULT 0
        CHECK (price_per_person >= 0)
);

CREATE TABLE IF NOT EXISTS excursion_dates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    excursion_id INTEGER NOT NULL,
    excursion_date TEXT NOT NULL,
    capacity INTEGER NOT NULL DEFAULT 20
        CHECK (capacity > 0),

    FOREIGN KEY (excursion_id) REFERENCES excursions(id),
    UNIQUE (excursion_id, excursion_date)
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
    total_price REAL NOT NULL DEFAULT 0
        CHECK (total_price >= 0),
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

INSERT OR IGNORE INTO excursions
    (name, description, price_per_person)
VALUES
    ('Обзорная по городу', 'Знакомство с основными достопримечательностями города.', 1500),
    ('Музейный квартал', 'Посещение музеев и культурных мест.', 1200),
    ('Загородный эко-маршрут', 'Прогулка по природным местам за городом.', 2000);

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

INSERT OR IGNORE INTO excursion_dates
    (excursion_id, excursion_date, capacity)
VALUES
    (1, '2026-11-14', 20),
    (1, '2026-11-15', 20),
    (1, '2026-11-21', 20),
    (1, '2026-11-22', 20),
    (1, '2026-11-28', 20),
    (1, '2026-11-29', 20),

    (2, '2026-11-14', 20),
    (2, '2026-11-15', 20),
    (2, '2026-11-21', 20),
    (2, '2026-11-22', 20),
    (2, '2026-11-28', 20),
    (2, '2026-11-29', 20),

    (3, '2026-11-14', 20),
    (3, '2026-11-15', 20),
    (3, '2026-11-21', 20),
    (3, '2026-11-22', 20),
    (3, '2026-11-28', 20),
    (3, '2026-11-29', 20);