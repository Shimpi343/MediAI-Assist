-- Enhanced Doctor Consultation Database Schema
CREATE DATABASE IF NOT EXISTS doctor_db;
USE doctor_db;

-- USERS TABLE (Authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- CONSULTATIONS TABLE (Enhanced)
CREATE TABLE IF NOT EXISTS consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    symptoms TEXT,
    diagnosis TEXT,
    confidence_score FLOAT DEFAULT 0,
    top_3_diseases TEXT, -- JSON format: ["Disease1", "Disease2", "Disease3"]
    severity_level ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Low',
    precautions TEXT,
    doctor_advice TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- DOCTORS TABLE (For appointments)
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(15),
    available_status BOOLEAN DEFAULT TRUE,
    experience_years INT,
    rating FLOAT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- APPOINTMENTS TABLE
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    consultation_id INT,
    appointment_date DATETIME NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL
);

-- DISEASE_DATABASE TABLE (For ML training reference)
CREATE TABLE IF NOT EXISTS disease_database (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disease_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    symptoms TEXT, -- JSON array of symptoms
    precautions TEXT,
    severity ENUM('Low', 'Medium', 'High', 'Critical'),
    treatment_guidelines TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CHAT_HISTORY TABLE
CREATE TABLE IF NOT EXISTS chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_message TEXT NOT NULL,
    bot_response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ANALYTICS TABLE (For dashboard)
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    total_consultations INT DEFAULT 0,
    disease_name VARCHAR(100),
    count INT DEFAULT 0,
    severity_level ENUM('Low', 'Medium', 'High', 'Critical'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for better performance
CREATE INDEX idx_user_id ON consultations(user_id);
CREATE INDEX idx_doctor_id ON appointments(doctor_id);
CREATE INDEX idx_created_at ON consultations(created_at);
CREATE INDEX idx_appointment_date ON appointments(appointment_date);

-- Insert sample data for diseases
INSERT INTO disease_database (disease_name, severity, symptoms, precautions) VALUES
('COVID-19', 'High', '["Fever","Cough","Shortness of Breath","Loss of Taste","Loss of Smell"]', 'Isolate, Wear mask, Get vaccinated'),
('Common Cold', 'Low', '["Runny Nose","Sore Throat","Cough","Sneezing"]', 'Rest, Fluids, Warm food'),
('Flu', 'Medium', '["Fever","Body Ache","Fatigue","Cough"]', 'Antiviral medication, Rest, Fluids'),
('Migraine', 'Medium', '["Headache","Fatigue","Dizziness","Nausea"]', 'Rest in dark room, Hydrate, Pain medication'),
('Food Poisoning', 'Medium', '["Nausea","Vomiting","Diarrhea","Stomach Pain"]', 'Electrolytes, Rest, Light diet'),
('Allergic Reaction', 'Medium', '["Skin Rash","Swelling","Itching"]', 'Antihistamines, Avoid allergen, Moisturize'),
('Diabetes', 'High', '["Frequent Urination","Increased Thirst","Fatigue","Blurred Vision"]', 'Monitor blood sugar, Balanced diet, Exercise'),
('Hypertension', 'High', '["Headache","Dizziness","Fatigue"]', 'Low sodium diet, Exercise, Medication'),
('Asthma', 'Medium', '["Shortness of Breath","Wheezing","Cough","Chest Tightness"]', 'Avoid triggers, Use inhaler, Regular check-ups'),
('Arthritis', 'Medium', '["Joint Pain","Swelling","Stiffness"]', 'Physical therapy, Rest, Anti-inflammatory medication');
