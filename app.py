# app.py
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
UPLOAD_FOLDER = 'static/uploaded'
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

if not os.path.exists(UPLOAD_FOLDER):
    os.makedirs(UPLOAD_FOLDER)

# Load model
try:
    model = load_model("model/skin_type_model.h5")
except Exception as e:
    print("⚠️ Model loading failed:", e)
    model = None

SERPER_API_KEY = os.getenv("SERPER_API_KEY")

def extract_price_from_text(text):
    """Extract LKR price from snippet"""
    match = re.search(r'(?:Rs\.?|LKR)\s*([\d,]+)', text, re.IGNORECASE)
    if match:
        return f"LKR {match.group(1)}"
    return "Price: Check site"

def search_ayurvedic_products(skin_type, base_keyword):
    """Search for authentic Sri Lankan Ayurvedic products"""
    if not SERPER_API_KEY:
        return get_fallback_ayurvedic_products(skin_type)

    # Focus ONLY on Sri Lankan Ayurvedic sources
    query = (
        f"best {base_keyword} for {skin_type} skin "
        f"site:healthguard.lk OR site:kapruka.com OR site:herbalconcepts.lk "
        f"OR site:siddhalepa.com OR site:wickramasiri.com OR site:ayurveda.gov.lk"
    )

    url = "https://google.serper.dev/search"  # ✅ FIXED: no extra spaces
    payload = {
        "q": query,
        "num": 3,
        "gl": "lk",
        "hl": "en"
    }
    headers = {
        'X-API-KEY': SERPER_API_KEY,
        'Content-Type': 'application/json'
    }

    try:
        response = requests.post(url, json=payload, headers=headers, timeout=10)
        if response.status_code != 200:
            return get_fallback_ayurvedic_products(skin_type)

        data = response.json()
        products = []
        for item in data.get('organic', [])[:3]:
            title = item.get('title', 'Ayurvedic Product')[:80]
            snippet = item.get('snippet', 'Natural skincare')
            link = item.get('link', '#')
            price = extract_price_from_text(snippet)
            
            products.append({
                "name": title,
                "description": f"{snippet[:100]}... • {price}",
                "link": link
            })
        return products or get_fallback_ayurvedic_products(skin_type)
    except Exception as e:
        print("🔍 Search error:", str(e))
        return get_fallback_ayurvedic_products(skin_type)

def get_fallback_ayurvedic_products(skin_type):
    """Curated list of trusted Sri Lankan Ayurvedic products"""
    fallbacks = {
        "oily": [
            {"name": "Siddhalepa Neem Face Wash", "description": "Purifies oily skin with natural neem • Price: LKR 850", "link": "https://www.siddhalepa.com/neem-face-wash"},
            {"name": "Wickramasiri Sandalwood Soap", "description": "Reduces oil & acne with pure sandalwood • Price: LKR 450", "link": "https://wickramasiri.com/products/sandalwood-soap"},
            {"name": "Herbal Concepts Turmeric Face Wash", "description": "Antibacterial herbal formula • Price: LKR 750", "link": "https://herbalconcepts.lk/product/turmeric-face-wash"}
        ],
        "dry": [
            {"name": "Siddhalepa Aloe Vera Cream", "description": "Deeply hydrates dry skin naturally • Price: LKR 950", "link": "https://www.siddhalepa.com/aloe-vera-cream"},
            {"name": "Herbal Concepts Coconut-Almond Oil", "description": "Nourishing blend for dry skin • Price: LKR 1,200", "link": "https://herbalconcepts.lk/product/coconut-almond-oil"},
            {"name": "Wickramasiri Rose Moisturizer", "description": "Gentle daily hydration • Price: LKR 800", "link": "https://wickramasiri.com/products/rose-moisturizer"}
        ],
        "normal": [
            {"name": "Siddhalepa Sandalwood Face Pack", "description": "Balances & brightens normal skin • Price: LKR 750", "link": "https://www.siddhalepa.com/sandalwood-face-pack"},
            {"name": "Herbal Concepts Cinnamon Toner", "description": "Refreshing daily toner • Price: LKR 650", "link": "https://herbalconcepts.lk/product/cinnamon-toner"},
            {"name": "Wickramasiri Aloe Vera Gel", "description": "Lightweight daily care • Price: LKR 550", "link": "https://wickramasiri.com/products/aloe-vera-gel"}
        ]
    }
    return fallbacks.get(skin_type, fallbacks["normal"])

@app.route("/api/predict", methods=["POST"])
def api_predict():
    if model is None:
        return jsonify({"error": "AI model not loaded"}), 500

    file = request.files.get('image')
    if not file or file.filename == '':
        return jsonify({"error": "No file uploaded"}), 400

    filepath = os.path.join(app.config['UPLOAD_FOLDER'], file.filename)
    try:
        file.save(filepath)
    except Exception as e:
        return jsonify({"error": "File save failed"}), 500

    img = cv2.imread(filepath)
    if img is None:
        return jsonify({"error": "Invalid image"}), 400

    img = cv2.resize(img, (224, 224))
    img = img.astype(np.float32) / 255.0
    img = np.expand_dims(img, axis=0)

    prediction = model.predict(img)[0]
    class_names = ['dry', 'normal', 'oily']
    predicted_index = int(np.argmax(prediction))
    skin_type = class_names[predicted_index]

    keyword_map = {
        "oily": "Ayurvedic face wash",
        "dry": "natural moisturizer",
        "normal": "herbal daily care"
    }
    product_keyword = keyword_map[skin_type]
    products = search_ayurvedic_products(skin_type, product_keyword)

    return jsonify({
        "skin_type": skin_type,
        "confidence": round(float(prediction[predicted_index]) * 100, 2),
        "oily_level": round(float(prediction[2]) * 100, 2),
        "dry_level": round(float(prediction[0]) * 100, 2),
        "normal_level": round(float(prediction[1]) * 100, 2),
        "products": products,
        "filename": file.filename
    })

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=True)