# Use PHP with Apache
FROM php:8.2-apache

# Install Python and dependencies
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

# Copy project files
COPY . /var/www/html/

# Install PHP extensions (MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Install Python requirements
RUN pip3 install --no-cache-dir -r /var/www/html/requirements.txt

# Enable Apache Mod-Rewrite for routing
RUN a2enmod rewrite

# Update Apache config to allow .htaccess and set DocumentRoot
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set environment variables for Apache
ENV PORT=80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
