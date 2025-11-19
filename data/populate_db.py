import csv
import mysql.connector
import re
from difflib import SequenceMatcher
import logging

# === CONFIGURAZIONE LOG (opzionale) ===
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# === CONNESSIONE DATABASE ===
db = mysql.connector.connect(
    host="localhost",
    user="root",        # Cambia se necessario
    password="",        # Inserisci password
    database="mysecretchef",
    port=3306,
    autocommit=False
)
cursor = db.cursor()

# === FUNZIONI DI SUPPORTO ===
def normalize(s):
    """Rimuove caratteri non alfabetici e converte in minuscolo"""
    return re.sub(r'[^a-z]', '', s.lower())

def similarity(a, b):
    """Calcola somiglianza tra due stringhe"""
    return SequenceMatcher(None, a, b).ratio()

def best_match_tag(full_ing, tags):
    """Trova il tag più simile a full_ing tra i tags"""
    full_norm = normalize(full_ing)
    words = full_norm.split()
    last_word = words[-1] if words else ""

    best_tag = None
    best_score = 0

    for tag in tags:
        tag_norm = normalize(tag)
        score = 0

        # 1. Sottostringa esatta
        if tag_norm in full_norm:
            score = 1.0
        else:
            # 2. Somiglianza con ultima parola o frase completa
            score = max(
                similarity(tag_norm, last_word),
                similarity(tag_norm, full_norm)
            )

        if score > best_score and score >= 0.6:
            best_score = score
            best_tag = tag

    return best_tag

# === 1. POPOLA TABELLA NUTRIENT ===
nutrient_names = [
    "Total Fat", "Saturated Fat", "Total Carbohydrate",
    "Dietary Fiber", "Total Sugars", "Protein"
]

logger.info("Inserimento nutrienti...")
for name in nutrient_names:
    cursor.execute("INSERT IGNORE INTO nutrient (name) VALUES (%s)", (name,))
db.commit()

# Recupera ID nutrienti
nutrient_ids = {}
cursor.execute("SELECT id, name FROM nutrient")
for nid, name in cursor.fetchall():
    nutrient_ids[name] = nid

# === 2. RACCOLTA INGREDIENTI UNIVOCI DA TAGS ===
logger.info("Raccolta ingredienti univoci da tags...")
unique_ingredients = set()

with open("recipes_cleaned.csv", "r", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    for row in reader:
        if row["tags"]:
            tags = [t.strip() for t in row["tags"].split(",") if t.strip()]
            unique_ingredients.update(tags)

# === 3. INSERISCI INGREDIENTI UNIVOCI ===
logger.info(f"Inserimento {len(unique_ingredients)} ingredienti univoci...")
for ing in sorted(unique_ingredients):
    cursor.execute("INSERT IGNORE INTO ingredient (name) VALUES (%s)", (ing,))
db.commit()

# Recupera ID ingredienti
ingredient_ids = {}
cursor.execute("SELECT id, name FROM ingredient")
for iid, name in cursor.fetchall():
    ingredient_ids[name] = iid

# === 4. POPOLA RICETTE, NUTRIENTI E INGREDIENTI ===
logger.info("Popolamento ricette, nutrienti e ingredienti...")
with open("recipes_cleaned.csv", "r", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    recipe_count = 0

    for row in reader:
        recipe_count += 1
        if recipe_count % 50 == 0:
            logger.info(f"Elaborate {recipe_count} ricette...")

        # --- RICETTA ---
        name = row["recipe_name"].strip()
        instructions = row["directions"].strip()
        category = row["category"]
        image_url = row["img_src"]
        prep_time = int(row["total_mins"]) if row["total_mins"].strip() else None

        cursor.execute("""
            INSERT INTO recipe (name, instructions, category, image_url, prep_time)
            VALUES (%s, %s, %s, %s, %s)
        """, (name, instructions, category, image_url, prep_time))
        recipe_id = cursor.lastrowid

        # --- NUTRIENTI ---
        nutrition_str = row["nutrition"]
        nut_values = {}
        if nutrition_str:
            for part in nutrition_str.split(","):
                if ":" in part:
                    k, v = part.split(":", 1)
                    k = k.strip()
                    try:
                        v = float(v.strip())
                    except:
                        v = 0
                    nut_values[k] = v

        for nut_name in nutrient_names:
            value = nut_values.get(nut_name, 0)
            nut_id = nutrient_ids[nut_name]
            cursor.execute("""
                INSERT INTO recipe_nutrient (recipe_id, nutrient_id, value)
                VALUES (%s, %s, %s)
            """, (recipe_id, nut_id, value))

        # --- INGREDIENTI PARSED + MATCHING ---
        tags = [t.strip() for t in row["tags"].split(",") if t.strip()]
        parsed = re.findall(r"quantity: ([\d.]+) unit: (\S+) ingredient: (.*?)(?= quantity:|$)", row["ingredients_parsed"])

        ingredient_dict = {}

        for quant_str, unit, full_ing in parsed:
            full_ing = full_ing.strip()
            if not full_ing or not tags:
                continue
            try:
                quantity = float(quant_str)
            except:
                continue

            # Trova il miglior tag
            best_tag = best_match_tag(full_ing, tags)
            if not best_tag or best_tag not in ingredient_ids:
                continue

            if best_tag not in ingredient_dict:
                ingredient_dict[best_tag] = {"quantity": 0, "unit": unit, "raws": []}

            entry = ingredient_dict[best_tag]
            if entry["unit"] != unit:
                continue  # Unità diverse: salta (o converti in futuro)

            entry["quantity"] += quantity
            entry["raws"].append(full_ing)

        # --- INSERISCI recipe_ingredient ---
        for tag, entry in ingredient_dict.items():
            quantity = round(entry["quantity"], 2)
            unit = entry["unit"]
            raw_ingredient = ", ".join(entry["raws"])[:30]  # max 30 caratteri
            ing_id = ingredient_ids[tag]

            cursor.execute("""
                INSERT INTO recipe_ingredient
                (recipe_id, ingredient_id, raw_ingredient, quantity, unit)
                VALUES (%s, %s, %s, %s, %s)
            """, (recipe_id, ing_id, raw_ingredient, quantity, unit))

    db.commit()
    logger.info(f"Popolamento completato: {recipe_count} ricette inserite.")

# === CHIUSURA ===
cursor.close()
db.close()
logger.info("Connessione chiusa. Database popolato con successo!")