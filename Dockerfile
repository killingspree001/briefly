# Use PHP with Apache
FROM php:8.2-apache

# Install Python and balance dependencies
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (pdo_mysql is the key one)
RUN docker-php-ext-install pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install Python requirements
# Note: Added --break-system-packages for compatibility with newer Debian/Python versions
RUN pip3 install --no-cache-dir --break-system-packages -r requirements.txt || \
    (python3 -m venv venv && ./venv/bin/pip install -r requirements.txt)

# Enable Apache Mod-Rewrite for routing
RUN a2enmod rewrite

# Update Apache config to allow .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set environment variables for Apache
ENV PORT=80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
