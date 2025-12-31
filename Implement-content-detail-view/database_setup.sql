CREATE DATABASE tourism_management;

USE tourism_management;

CREATE TABLE attractions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    short_description TEXT NOT NULL,
    full_description TEXT NOT NULL,
    image_url VARCHAR(2083) NOT NULL
);

INSERT INTO attractions (name, short_description, full_description, image_url) VALUES
('Axum', 'Ancient city known for obelisks.', 'Axum is one of Ethiopia’s oldest cities and was the center of the Axumite Kingdom. It is famous for its giant stone obelisks and historical importance.', 'https://upload.wikimedia.org/wikipedia/commons/3/3b/Axum_Stelae_Park.jpg'),
('Lalibela', 'Rock-hewn churches.', 'Lalibela is famous for its 11 medieval rock-hewn churches carved from solid rock and is a UNESCO World Heritage Site.', 'https://upload.wikimedia.org/wikipedia/commons/7/7e/Lalibela_church.jpg'),
('Sof Omar Cave', 'Largest cave system in Ethiopia.', 'Sof Omar Cave is a spectacular limestone cave system formed by the Web River and holds religious and natural significance.', 'https://upload.wikimedia.org/wikipedia/commons/1/1e/Sof_Omar_Cave.jpg'),
('Harar Jugol', 'Historic walled city.', 'Harar Jugol is known as the fourth holiest city of Islam and is famous for its old walls, mosques, and cultural heritage.', 'https://upload.wikimedia.org/wikipedia/commons/8/86/Harar_Jugol.jpg'),
('Tiya (Tiya Stelae)', 'Ancient stone monuments.', 'Tiya is an archaeological site with mysterious carved standing stones, representing an ancient Ethiopian culture.', 'https://upload.wikimedia.org/wikipedia/commons/9/9a/Tiya_stelae.jpg'),
('Fasil Ghebbi (Gondar)', 'Royal castles complex.', 'Fasil Ghebbi is a fortress-city containing castles built in the 17th century and is a UNESCO World Heritage Site.', 'https://upload.wikimedia.org/wikipedia/commons/f/f6/Fasil_Ghebbi.jpg');