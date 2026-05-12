import sys
import json
import pickle
import numpy as np
import os

BASE_DIR = os.path.dirname(__file__)

with open(os.path.join(BASE_DIR, "ai_model.pkl"), "rb") as f:
    bundle = pickle.load(f)

# Load model + scaler
# with open("ai_model.pkl", "rb") as f:
#     bundle = pickle.load(f)

model = bundle["model"]
scaler = bundle["scaler"]

# Read input
input_data = json.loads(sys.stdin.read())

X = np.array([[
    input_data["expected_revenue"],
    input_data["cust_type"],
    input_data["wins"],
    input_data["lost"],
    input_data["activity"],
    input_data["source"]
]])

# SCALE input
X_scaled = scaler.transform(X)

# Predict
probability = model.predict_proba(X_scaled)[0][1]

print(json.dumps({
    "probability": round(probability * 100, 2)
}))
