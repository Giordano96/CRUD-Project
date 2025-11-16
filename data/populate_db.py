import pandas as pd
import mysql.connector
import re

# Connect to the database
conn = mysql.connector.connect(
    host='localhost',
    user='root',  # Change to your username
    password='',  # Change to your password
    database='mysecretchef'
)
cursor = conn.cursor()

# Step 1: Populate nutrient table
nutrient_names = [
    'Total Fat', 'Saturated Fat', 'Total Carbohydrate',
    'Dietary Fiber', 'Total Sugars', 'Protein'
]
for name in nutrient_names:
    cursor.execute("INSERT IGNORE INTO nutrient (name) VALUES (%s)", (name,))
conn.commit()

# Get nutrient ids
cursor.execute("SELECT id, name FROM nutrient")
nutrient_dict = {row[1]: row[0] for row in cursor.fetchall()}

# Step 2: Read the CSV
df = pd.read_csv('recipes_cleaned.csv')

# Step 3: Collect unique ingredients from tags
unique_ingredients = set()
for tags in df['tags']:
    if pd.notna(tags):
        unique_ingredients.update([tag.strip() for tag in tags.split(',')])

# Insert unique ingredients
for ing in unique_ingredients:
    cursor.execute("INSERT IGNORE INTO ingredient (name) VALUES (%s)", (ing,))
conn.commit()

# Get ingredient ids
cursor.execute("SELECT id, name FROM ingredient")
ingredient_dict = {row[1]: row[0] for row in cursor.fetchall()}

# Step 4: Populate recipe, recipe_nutrient, recipe_ingredient
for index, row in df.iterrows():
    # Insert recipe
    cursor.execute("""
        INSERT INTO recipe (name, instructions, category, image_url, prep_time)
        VALUES (%s, %s, %s, %s, %s)
    """, (row['recipe_name'], row['directions'], row['category'], row['img_src'], row['total_mins']))
    recipe_id = cursor.lastrowid
    conn.commit()

    # Parse and insert recipe_nutrient
    if pd.notna(row['nutrition']):
        nutrition_str = row['nutrition']
        nutrients = {}
        for part in nutrition_str.split(','):
            if ':' in part:
                name, value = part.split(':', 1)
                nutrients[name.strip()] = int(float(value.strip()))  # Since decimal(10,0)

        for nut_name, value in nutrients.items():
            if nut_name in nutrient_dict:
                cursor.execute("""
                    INSERT INTO recipe_nutrient (recipe_id, nutrient_id, value)
                    VALUES (%s, %s, %s)
                """, (recipe_id, nutrient_dict[nut_name], value))
    conn.commit()

    # Parse ingredients_parsed
    parsed_str = row['ingredients_parsed']
    if pd.notna(parsed_str):
        pattern = r"quantity: (\d+\.?\d*) unit: ([\w]+) ingredient: (.*?)(?= quantity:| $)"
        parsed_items = re.findall(pattern, parsed_str)

        # Get tags list
        tags_list = [tag.strip() for tag in row['tags'].split(',')] if pd.notna(row['tags']) else []

        # Crea un dizionario: ingrediente_parte → (quantity, unit)
        parsed_dict = {}
        for q_str, unit, ingredient in parsed_items:
            ingredient = ingredient.strip()
            q = float(q_str)
            # Somma quantità se stesso ingrediente appare più volte
            if ingredient in parsed_dict:
                parsed_dict[ingredient] = (parsed_dict[ingredient][0] + q, unit)
            else:
                parsed_dict[ingredient] = (q, unit)

        # Ora associa ogni tag a UN SOLO ingrediente parsed (match esatto o per parola intera)
        for tag in tags_list:
            tag_lower = tag.lower()
            matched_ingredient = None
            matched_qty = 0
            matched_unit = None

            # Cerca match esatto prima
            for ing, (q, u) in parsed_dict.items():
                if tag_lower == ing.lower():
                    matched_ingredient = ing
                    matched_qty = q
                    matched_unit = u
                    break

            # Se non c'è match esatto, cerca per parola intera (evita "ice" in "ice cream")
            if not matched_ingredient:
                for ing, (q, u) in parsed_dict.items():
                    ing_words = [w.strip() for w in ing.split()]
                    tag_words = tag_lower.split()
                    if all(word in [w.lower() for w in ing_words] for word in tag_words):
                        if len(tag_words) == len(ing_words):  # match completo
                            matched_ingredient = ing
                            matched_qty = q
                            matched_unit = u
                            break

            if matched_ingredient and matched_unit in ['bottle','can','cans','jar','jars','package','piece','g','ml']:
                if tag in ingredient_dict:
                    cursor.execute("""
                        INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity, unit)
                        VALUES (%s, %s, %s, %s)
                    """, (recipe_id, ingredient_dict[tag], matched_qty, matched_unit))
    conn.commit()

# Close connection
cursor.close()
conn.close()

print("Database populated successfully.")