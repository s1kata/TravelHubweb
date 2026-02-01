PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    phone TEXT,
    city TEXT,
    age INTEGER,
    gender TEXT,
    passport_series TEXT,
    passport_number TEXT,
    passport_issued_by TEXT,
    passport_issue_date DATE,
    passport_expiry_date DATE,
    role TEXT DEFAULT 'user',
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    status TEXT DEFAULT 'active',
    source TEXT DEFAULT 'website' CHECK(source IN ('website', 'app'))
);

CREATE TABLE IF NOT EXISTS user_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tour_title TEXT NOT NULL,
    hotel_name TEXT NOT NULL,
    destination TEXT NOT NULL,
    stars INTEGER,
    nights INTEGER,
    price NUMERIC(10,2),
    currency TEXT DEFAULT 'RUB',
    meals TEXT,
    departure_date DATE,
    return_date DATE,
    status TEXT DEFAULT 'pending',
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    source TEXT DEFAULT 'website' CHECK(source IN ('website', 'app')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    name TEXT,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active INTEGER DEFAULT 1,
    preferences TEXT
);

CREATE TABLE IF NOT EXISTS user_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tour_title TEXT NOT NULL,
    hotel_name TEXT NOT NULL,
    destination TEXT NOT NULL,
    stars INTEGER,
    nights INTEGER,
    price NUMERIC(10,2),
    currency TEXT DEFAULT 'RUB',
    meals TEXT,
    departure_date DATE,
    return_date DATE,
    status TEXT DEFAULT 'pending',
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    source TEXT DEFAULT 'website' CHECK(source IN ('website', 'app')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    name TEXT,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active INTEGER DEFAULT 1,
    preferences TEXT
);

CREATE TABLE IF NOT EXISTS user_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tour_title TEXT NOT NULL,
    hotel_name TEXT NOT NULL,
    destination TEXT NOT NULL,
    stars INTEGER,
    nights INTEGER,
    price NUMERIC(10,2),
    currency TEXT DEFAULT 'RUB',
    meals TEXT,
    departure_date DATE,
    return_date DATE,
    status TEXT DEFAULT 'pending',
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    source TEXT DEFAULT 'website' CHECK(source IN ('website', 'app')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    name TEXT,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active INTEGER DEFAULT 1,
    preferences TEXT
);

CREATE TABLE IF NOT EXISTS user_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tour_title TEXT NOT NULL,
    hotel_name TEXT NOT NULL,
    destination TEXT NOT NULL,
    stars INTEGER,
    nights INTEGER,
    price NUMERIC(10,2),
    currency TEXT DEFAULT 'RUB',
    meals TEXT,
    departure_date DATE,
    return_date DATE,
    status TEXT DEFAULT 'pending',
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    source TEXT DEFAULT 'website' CHECK(source IN ('website', 'app')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    name TEXT,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active INTEGER DEFAULT 1,
    preferences TEXT
);

CREATE TABLE IF NOT EXISTS user_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tour_title TEXT NOT NULL,
    hotel_name TEXT NOT NULL,
    destination TEXT NOT NULL,
    stars INTEGER,
    nights INTEGER,
    price NUMERIC(10,2),
    currency TEXT DEFAULT 'RUB',
    meals TEXT,
    departure_date DATE,
    return_date DATE,
    status TEXT DEFAULT 'pending',
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    source TEXT DEFAULT 'website' CHECK(source IN ('website', 'app')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    name TEXT,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active INTEGER DEFAULT 1,
    preferences TEXT
);

CREATE TABLE IF NOT EXISTS tours (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    subtitle TEXT,
    destination TEXT NOT NULL,
    description TEXT,
    image_url TEXT,
    price_from NUMERIC(10,2),
    nights_min INTEGER,
    nights_max INTEGER,
    rating REAL,
    reviews_count INTEGER,
    badge TEXT,
    tag_line TEXT,
    spotlight_headline TEXT,
    spotlight_text TEXT,
    spotlight_price_label TEXT,
    spotlight_price_old NUMERIC(10,2),
    feature_rank INTEGER,
    spotlight_rank INTEGER,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tour_tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tour_id INTEGER NOT NULL,
    label TEXT NOT NULL,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_tours_destination ON tours(destination);
CREATE INDEX IF NOT EXISTS idx_tours_feature_rank ON tours(feature_rank);
CREATE INDEX IF NOT EXISTS idx_tours_spotlight_rank ON tours(spotlight_rank);
CREATE INDEX IF NOT EXISTS idx_tour_tags_tour_id ON tour_tags(tour_id);

CREATE TABLE IF NOT EXISTS country_content (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    country_slug TEXT NOT NULL UNIQUE,
    bio TEXT,
    highlights TEXT,
    useful_info TEXT,
    detailed_info TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INTEGER,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);CREATE INDEX IF NOT EXISTS idx_country_content_slug ON country_content(country_slug);CREATE TABLE IF NOT EXISTS exclusive_tours (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    subtitle TEXT,
    description TEXT,
    country_slug TEXT NOT NULL,
    image_url TEXT,
    blocks TEXT, -- JSON array of blocks with type, content, image
    display_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INTEGER,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);CREATE INDEX IF NOT EXISTS idx_exclusive_tours_country ON exclusive_tours(country_slug);
CREATE INDEX IF NOT EXISTS idx_exclusive_tours_order ON exclusive_tours(display_order);CREATE TABLE IF NOT EXISTS vip_hotels (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    city TEXT NOT NULL CHECK(city IN ('Antalya', 'Belek', 'Kemer')),
    rating TEXT DEFAULT '5*',
    description TEXT,
    bio TEXT,
    cuisine TEXT,
    meal_plan TEXT,
    images TEXT, -- JSON array of image paths
    features TEXT, -- JSON array of features
    location TEXT,
    beach_type TEXT,
    distance_to_airport TEXT,
    check_in_time TEXT,
    check_out_time TEXT,
    detailed_info TEXT, -- JSON: infrastructure, entertainment, spa, for_children, etc.
    display_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INTEGER,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);CREATE INDEX IF NOT EXISTS idx_vip_hotels_city ON vip_hotels(city);
CREATE INDEX IF NOT EXISTS idx_vip_hotels_slug ON vip_hotels(slug);
CREATE INDEX IF NOT EXISTS idx_vip_hotels_order ON vip_hotels(display_order);
CREATE INDEX IF NOT EXISTS idx_vip_hotels_active ON vip_hotels(is_active);