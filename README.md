# 🍽️ CRUD-Project

A **web-app** project to manage available pantry products and suggest recipes that can be cooked with them.  

The system is based on the analysis of a recipes dataset available on Kaggle:  
➡️ [Better Recipes for a Better Life](https://www.kaggle.com/datasets/thedevastator/better-recipes-for-a-better-life?resource=download)

---

## 🎯 Goals
- CRUD management (create, read, update, delete).  
- Link between available ingredients and possible recipes.  
- Dataset analysis to extract useful insights from recipes.  
- Build a working prototype using Python and web tools.  

---

## 📂 Dataset
- **Source:** Kaggle  
- **Name:** Better Recipes for a Better Life  
- **Description:** Contains recipes with ingredients, instructions, categories, and other useful information for analysis.  

---

## 📌 Milestones (to modify)
- ✅ Project research  
- ✅ Dataset research  
- ✅ Preliminary dataset analysis (EDA)  
- ✅ Data cleaning and preparation  
- ✅ CRUD functionality implementation  
- ✅ Pantry–dataset–recipes integration
- ✅ containerize using docker 
- ✅ Tableu report
- ✅ Testing and documentation  

---

## 🛠️ Technologies 
- **Backend:**  PHP
- **Database:** MariaDb (phpMyAdmin)  
- **Frontend:** HTML, CSS, JavaScript  
- **Data Analysis:** Python  
- **Container:** Docker, Docker Desktop

---

## 📖 Repository Structure 
```
├── data/               # Dataset and related files
├── src/                # Web-app source code
├── docs/               # Documentation
├── docker-compose.yml  # Docker
├── Dockerfile          # Docker
└── README.md           # This file
```

---

## 👥 Team & Roles
- Client contact:
- Edoardo Moretti
- Technical advisor:
- Marco Amici
- Team members:
- Giordano Sancricca (PM)
- Lorenzo Uccellani (Backend Supervisor)
- Alessio Parlani (Frontend Supervisor)
- Jhonatan Panico (UI and Frontend)
- Giorgio Leonard Dahore (Backend)
- Carlo Perella (Documentation)

---

## 🚀 How to use
1. Download Docker Desktop and open it 
2. Clone the repository
3. Create on the root of the project a file .env for insert the credentials on the dockercompose
4. Open a terminal inside the dir CRUD-Project and use the command docker-compose up -d
5. Open the browser and go to localhost:8080 to interact with the app
6. localhost:8081 to interact with the DB 
