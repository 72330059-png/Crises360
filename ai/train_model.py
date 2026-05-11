import json
import pickle
import numpy as np
import os
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler

BASE_DIR = os.path.dirname(__file__)

# 1️⃣ Load dataset exported by PHP
with open(os.path.join(BASE_DIR, "ai_dataset.json"), "r") as f:
    data = json.load(f)

X = []
y = []

for row in data:
    X.append([
        row["expected_revenue"],
        row["cust_type"],
        row["wins"],
        row["lost"],
        row["activity"],
        row["source"]
    ])
    y.append(row["outcome"])

X = np.array(X)
y = np.array(y)

# 2️⃣ Scale
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# 3️⃣ Train
model = LogisticRegression(class_weight="balanced")
model.fit(X_scaled, y)

# 4️⃣ Save model + scaler
with open(os.path.join(BASE_DIR, "ai_model.pkl"), "wb") as f:
    pickle.dump({
        "model": model,
        "scaler": scaler
    }, f)

print("✅ AI model trained & saved correctly")
