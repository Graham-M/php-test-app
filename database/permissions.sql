CREATE USER 'weather'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT on weather_data.* to 'weather'@'localhost';
