import random
import datetime
import mysql.connector

# Define the list of European cities
european_cities = [
    'Amsterdam',
    'Barcelona',
    'Berlin',
    'Brussels',
    'Budapest',
    'Copenhagen',
    'Dublin',
    'Helsinki',
    'Krakow',
    'Lisbon',
    'Madrid',
    'Milan',
    'Oslo',
    'Paris',
    'Prague',
    'Rome',
    'Stockholm',
    'Vienna',
    'Warsaw',
    'Zurich'
]

# Connect to the MySQL database
db = mysql.connector.connect(
  host="localhost",
  user="yourusername",
  password="yourpassword",
  database="weather_data"
)

# Generate and insert random weather data for each city
for city in european_cities:
    # Generate random weather data for the past 7 days
    for i in range(7):
        day = datetime.date.today() - datetime.timedelta(days=i)
        for hour in range(0, 24):
            temperature = random.uniform(0, 30)
            precipitation = random.uniform(0, 10)
            humidity = random.uniform(0, 100)

            # Insert the weather data into the MySQL database
            cursor = db.cursor()
            sql = "INSERT INTO weather (city, day, hour, temperature, precipitation, humidity) VALUES (%s, %s, %s, %s, %s, %s)"
            values = (city, day, hour, temperature, precipitation, humidity)
            cursor.execute(sql, values)

            db.commit()

print("Weather data generated and inserted into the database.")
