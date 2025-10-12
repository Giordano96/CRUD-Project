import pandas as pd
import re

def extract_ingredients(parsed_str):
    # Check if parsed_str is a string; if not (e.g., NaN), return empty list
    if not isinstance(parsed_str, str):
        return []
    # Split the string into groups starting with "quantity:"
    groups = re.split(r', quantity:', parsed_str)[1:]  # Skip anything before first quantity
    groups = ['quantity:' + g for g in groups]  # Reattach quantity: for consistency
    ingredients = []
    for group in groups:
        # Split each group into quantity, unit, ingredient
        parts = re.split(r', unit: |, ingredient: ', group)
        if len(parts) == 3:
            ingredient = parts[2].strip()  # Extract the ingredient part
            ingredients.append(ingredient)
    return ingredients

# Load the DataFrame
df = pd.read_csv("recipes_with_parsed_ingredients.csv")

# Extract all ingredients from the ingredients_parsed column
all_ingredients = []
for parsed_str in df['ingredients_parsed']:
    ingredients = extract_ingredients(parsed_str)
    all_ingredients.extend(ingredients)

# Get unique ingredients
unique_ingredients = sorted(list(set(all_ingredients)))

# Print the list of unique ingredients
print("List of all unique ingredients:")
for idx, ingredient in enumerate(unique_ingredients, 1):
    print(f"{idx}. {ingredient}")

# Optionally save the list to a file
with open("unique_ingredients.txt", "w") as f:
    for ingredient in unique_ingredients:
        f.write(f"{ingredient}\n")
print("\nUnique ingredients saved to 'unique_ingredients.txt'")