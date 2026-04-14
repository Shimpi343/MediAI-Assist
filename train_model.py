"""
AI Disease Prediction Model using Logistic Regression
This script trains an ML model to predict diseases based on symptoms
"""

import json
import pickle
from sklearn.ensemble import RandomForestClassifier
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import LabelEncoder
import numpy as np

# Dataset: Symptoms and corresponding diseases
training_data = {
    'Fever, Cough, Shortness of Breath, Loss of Taste': 'COVID-19',
    'Fever, Cough, Shortness of Breath': 'Pneumonia',
    'Headache, Fatigue, Dizziness': 'Migraine',
    'Headache, Fatigue, Dizziness, Nausea': 'Migraine',
    'Nausea, Vomiting, Diarrhea': 'Food Poisoning',
    'Nausea, Vomiting, Diarrhea, Stomach Pain': 'Gastroenteritis',
    'Skin Rash, Swelling, Itching': 'Allergic Reaction',
    'Back Pain, Joint Pain, Stiffness': 'Arthritis',
    'Chest Pain': 'Cardiac Issue',
    'Fever, Body Ache, Fatigue': 'Flu',
    'Runny Nose, Sore Throat, Cough': 'Common Cold',
    'Shortness of Breath, Wheezing, Chest Tightness': 'Asthma',
    'Frequent Urination, Increased Thirst, Fatigue': 'Diabetes',
    'Headache, Dizziness, Fatigue': 'Hypertension',
    'Constipation, Stomach Pain': 'IBS',
    'Fever, Sore Throat': 'Strep Throat',
    'Cough, Wheezing, Shortness of Breath': 'Bronchitis',
    'Weakness, Fatigue, Loss of Appetite': 'Hepatitis',
    'Eye Redness, Itching, Discharge': 'Conjunctivitis',
    'Joint Pain, Swelling, Stiffness, Fatigue': 'Rheumatoid Arthritis',
}

# Prepare data
X_symptoms = list(training_data.keys())
y_diseases = list(training_data.values())

# Vectorize symptoms using TF-IDF
vectorizer = TfidfVectorizer(lowercase=True, stop_words='english')
X = vectorizer.fit_transform(X_symptoms).toarray()

# Encode disease labels
label_encoder = LabelEncoder()
y = label_encoder.fit_transform(y_diseases)

# Train Random Forest Classifier (Better than Logistic Regression for this)
model = RandomForestClassifier(n_estimators=100, random_state=42, max_depth=10)
model.fit(X, y)

# Save model and vectorizer
with open('disease_model.pkl', 'wb') as f:
    pickle.dump(model, f)

with open('vectorizer.pkl', 'wb') as f:
    pickle.dump(vectorizer, f)

with open('label_encoder.pkl', 'wb') as f:
    pickle.dump(label_encoder, f)

print("✅ Model trained and saved successfully!")
print(f"Classes: {label_encoder.classes_}")

# Test prediction
test_symptoms = "Fever, Cough, Shortness of Breath"
test_vector = vectorizer.transform([test_symptoms]).toarray()
prediction = model.predict(test_vector)
probabilities = model.predict_proba(test_vector)[0]

print(f"\n🧪 Test Prediction:")
print(f"Symptoms: {test_symptoms}")
print(f"Predicted Disease: {label_encoder.inverse_transform(prediction)[0]}")
print(f"Confidence: {max(probabilities)*100:.2f}%")

# Top 3 predictions
top_3_indices = np.argsort(probabilities)[-3:][::-1]
print(f"\nTop 3 Possible Diseases:")
for idx, class_idx in enumerate(top_3_indices, 1):
    print(f"{idx}. {label_encoder.classes_[class_idx]} - {probabilities[class_idx]*100:.2f}%")
