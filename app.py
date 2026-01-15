from flask import Flask, request, jsonify
import os
import cv2
import numpy as np
import requests
import re
from tensorflow.keras.models import load_model
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)

# Upload folder
UPLOAD_FOLDER = 'static/uploaded'
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

# Load model
try:
    model = load_model("model/skin_type_model.h5")
    print("✅ Model loaded successfully")
except Exception as e:
    print("⚠️ Model loading failed:", e)
    model = None

SERPER_API_KEY = os.getenv("SERPER_API_KEY")

def extract_price_from_text(text):
    match = re.search(r'(?:Rs\.?|LKR)\s*([\d,]+)', text, re.IGNORECASE)
    if match:
        return f"LKR {match.group(1)}"
    return "Price: Check site"

def search_ayurvedic_products(skin_type, base_keyword):
    if not SERPER_API_KEY:
        return get_fallback_ayurvedic_products(skin_type)

    query = (
        f"best {base_keyword} for {skin_type} skin "
        f"site:healthguard.lk OR site:kapruka.com OR site:herbalconcepts.lk "
        f"OR site:siddhalepa.com OR site:wickramasiri.com OR site:ayurveda.gov.lk"
    )

    url = "https://google.serper.dev/search"
    payload = {"q": query, "num": 3, "gl": "lk", "hl": "en"}
    headers = {
        "X-API-KEY": SERPER_API_KEY,
        "Content-Type": "application/json"
    }

    try:
        response = requests.post(url, json=payload, headers=headers, timeout=10)
        data = response.json()

        products = []
        for item in data.get("organic", [])[:3]:
            products.append({
                "name": item.get("title", "Ayurvedic Product")[:80],
                "description": f"{item.get('snippet', '')[:100]} • {extract_price_from_text(item.get('snippet',''))}",
                "link": item.get("link", "#")
            })
        return products or get_fallback_ayurvedic_products(skin_type)
    except Exception as e:
        print("🔍 Search error:", e)
        return get_fallback_ayurvedic_products(skin_type)

def get_fallback_ayurvedic_products(skin_type):
    fallbacks = {
        "oily": [
            {"name": "Siddhalepa Neem Face Wash", "description": "Purifies oily skin • LKR 850", "link": "https://www.siddhalepa.com"},
        ],
        "dry": [
            {"name": "Siddhalepa Aloe Vera Cream", "description": "Hydrates dry skin • LKR 950", "link": "https://www.siddhalepa.com"},
        ],
        "normal": [
            {"name": "Wickramasiri Aloe Vera Gel", "description": "Daily care • LKR 550", "link": "https://wickramasiri.com"},
        ]
    }
    return fallbacks.get(skin_type, fallbacks["normal"])

@app.route("/")
def home():
    return "AI Beauty Salon API is running 🚀"

@app.route("/api/predict", methods=["POST"])
def api_predict():
    if model is None:
        return jsonify({"error": "AI model not loaded"}), 500

    file = request.files.get("image")
    if not file:
        return jsonify({"error": "No file uploaded"}), 400

    filepath = os.path.join(app.config["UPLOAD_FOLDER"], file.filename)
    file.save(filepath)

    img = cv2.imread(filepath)
    if img is None:
        return jsonify({"error": "Invalid image"}), 400

    img = cv2.resize(img, (224, 224)) / 255.0
    img = np.expand_dims(img, axis=0)

    prediction = model.predict(img)[0]
    class_names = ["dry", "normal", "oily"]
    idx = int(np.argmax(prediction))
    skin_type = class_names[idx]

    products = search_ayurvedic_products(skin_type, "Ayurvedic skincare")

    return jsonify({
        "skin_type": skin_type,
        "confidence": round(float(prediction[idx]) * 100, 2),
        "products": products
    })

# 🚆 RAILWAY ENTRY POINT (MOST IMPORTANT)
if __name__ == "__main__":
    port = int(os.environ.get("PORT", 8080))
    app.run(host="0.0.0.0", port=port)
