import pandas as pd
import re

def parse_nutrition(nutrition_str):
    if not isinstance(nutrition_str, str):
        return "Total Fat: 0, Saturated Fat: 0, Total Carbohydrate: 0, Dietary Fiber: 0, Total Sugars: 0, Protein: 0"

    result = {k: 0 for k in ["Total Fat", "Saturated Fat", "Total Carbohydrate", "Dietary Fiber", "Total Sugars", "Protein"]}

    pattern = r"(Total\s+Fat|Saturated\s+Fat|Total\s+Carbohydrate|Dietary\s+Fiber|Total\s+Sugars?|Sugars?|Protein)[\s:]*(\d+\.?\d*)\s*g"
    matches = re.finditer(pattern, nutrition_str, re.IGNORECASE)

    for match in matches:
        nutrient = match.group(1).strip()
        value = float(match.group(2))

        if re.match(r"Total\s+Fat", nutrient, re.IGNORECASE):
            result["Total Fat"] = value
        elif re.match(r"Saturated\s+Fat", nutrient, re.IGNORECASE):
            result["Saturated Fat"] = value
        elif re.match(r"Total\s+Carbohydrate", nutrient, re.IGNORECASE):
            result["Total Carbohydrate"] = value
        elif re.match(r"Dietary\s+Fiber|Fiber", nutrient, re.IGNORECASE):
            result["Dietary Fiber"] = value
        elif re.match(r"Total\s+Sugars?|Sugars?", nutrient, re.IGNORECASE):
            result["Total Sugars"] = value
        elif "Protein" in nutrient:
            result["Protein"] = value

    return ", ".join([f"{k}: {v}" for k, v in result.items()])

# Carica e processa
df = pd.read_csv("recipes_cleaned.csv")
df['nutrition'] = df['nutrition'].apply(parse_nutrition)
df.to_csv("recipes_cleaned.csv", index=False)

