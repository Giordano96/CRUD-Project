import pandas as pd
import mysql.connector
from mysql.connector import Error
import re

# Database connection configuration
db_config = {
    'host': 'localhost',
    'user': 'root',  # Replace with your MySQL username
    'password': '',  # Replace with your MySQL password
    'database': 'mysecretchef'
}

# Read CSV file
df = pd.read_csv('recipes_cleaned.csv')

# Connect to MySQL database
try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()

    # Insert nutrients
    nutrients = [
        ('Total Fat',),
        ('Saturated Fat',),
        ('Total Carbohydrate',),
        ('Dietary Fiber',),
        ('Total Sugars',),
        ('Protein',)
    ]
    cursor.executemany("INSERT INTO nutrient (name) VALUES (%s)", nutrients)

    # Extract unique ingredients from tags
    all_tags = set()
    for tags in df['tags']:
        if pd.notna(tags):  # Check for non-NaN tags
            tags_list = tags.split(', ')
            all_tags.update(tags_list)

    # Insert ingredients
    ingredient_map = {}
    for idx, tag in enumerate(all_tags, start=1):
        cursor.execute("INSERT INTO ingredient (id, name) VALUES (%s, %s)", (idx, tag))
        ingredient_map[tag] = idx

    # Function to parse nutrition values
    def parse_nutrition(nutrition_str):
        nutrition_dict = {}
        if pd.isna(nutrition_str):
            return nutrition_dict
        pattern = r"(\w+\s*\w*):\s*(\d+\.\d)"
        for match in re.finditer(pattern, nutrition_str):
            nutrient, value = match.groups()
            nutrition_dict[nutrient.strip()] = float(value)
        return nutrition_dict

    # Function to parse ingredients
    def parse_ingredients(ingredients_str):
        ingredients = []
        if pd.isna(ingredients_str) or not isinstance(ingredients_str, str):
            return ingredients  # Return empty list if input is NaN or not a string
        pattern = r"quantity:\s*([\d.]+)\s*unit:\s*([^\s]+)\s*ingredient:\s*([^\n]+)"
        for match in re.finditer(pattern, ingredients_str):
            quantity, unit, ingredient = match.groups()
            ingredients.append({
                'quantity': float(quantity),
                'unit': unit,
                'ingredient': ingredient.strip()
            })
        return ingredients

    # Insert recipes and related data
    for index, row in df.iterrows():
        # Insert into recipe table
        recipe_id = index + 1
        cursor.execute("""
            INSERT INTO recipe (id, name, instructions, image_url, prep_time, category, ingredients_list)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (
            recipe_id,
            row['recipe_name'],
            row['directions'] if pd.notna(row['directions']) else '',
            row['img_src'] if pd.notna(row['img_src']) else '',
            row['total_mins'] if pd.notna(row['total_mins']) else 0,
            row['category'] if pd.notna(row['category']) else 'Desserts',
            row['ingredients'] if pd.notna(row['ingredients']) else ''
        ))

        # Insert into recipe_nutrient
        nutrition = parse_nutrition(row['nutrition'])
        for nutrient_name, value in nutrition.items():
            cursor.execute("SELECT id FROM nutrient WHERE name = %s", (nutrient_name,))
            nutrient_id = cursor.fetchone()
            if nutrient_id:
                cursor.execute("""
                    INSERT INTO recipe_nutrient (recipe_id, nutrient_id, value)
                    VALUES (%s, %s, %s)
                """, (recipe_id, nutrient_id[0], value))

        # Insert into recipe_ingredient
        ingredients = parse_ingredients(row['ingredients_parsed'])
        for ingredient in ingredients:
            # Find ingredient ID
            ingredient_name = ingredient['ingredient']
            # Find the closest matching tag from ingredient_map
            matched_tag = None
            for tag in ingredient_map:
                if tag.lower() in ingredient_name.lower():
                    matched_tag = tag
                    break
            if matched_tag:
                ingredient_id = ingredient_map[matched_tag]
                unit = ingredient['unit']
                # Map CSV units to database enum
                unit_map = {
                    'g': 'ounce',
                    'ml': 'cup',
                    'piece': 'piece',
                    'package': 'package',
                    'can': 'can'
                }
                db_unit = unit_map.get(unit, 'cup')  # Default to cup if unit not found
                cursor.execute("""
                    INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity, unit)
                    VALUES (%s, %s, %s, %s)
                """, (recipe_id, ingredient_id, ingredient['quantity'], db_unit))

    # Commit changes
    conn.commit()
    print("Database populated successfully!")

except Error as e:
    print(f"Error: {e}")
finally:
    if conn.is_connected():
        cursor.close()
        conn.close()
        print("Database connection closed.")