CREATE DATABASE weather_data;

USE weather_data;

CREATE TABLE weather (
  id INT NOT NULL AUTO_INCREMENT,
  city VARCHAR(50) NOT NULL,
  day DATE NOT NULL,
  hour INT NOT NULL,
  temperature DECIMAL(5, 2),
  precipitation DECIMAL(5, 2),
  humidity DECIMAL(5, 2),
  PRIMARY KEY (id)
);
