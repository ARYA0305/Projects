import requests

def get_weather_data(location, api_key):
    site_url = "http://api.openweathermap.org/data/2.5/weather"
    params = {
        'q': location,  
        'appid': api_key,
        'units': 'metric'  
    }

    response = requests.get(site_url, params=params)
    
    if response.status_code == 200:
        data = response.json()
        print("\n Weather Information for:", data['name'])
        print(" Temperature:", data['main']['temp'], "°C")
        print(" Humidity:", data['main']['humidity'], "%")
        print(" Condition:", data['weather'][0]['description'].capitalize())
    else:
        print("\n Error fetching data. Please check the details again")

def main():
    print("<<< Command-Line Weather App >>>")
    api_key = input("Enter your API key: ").strip()
    location = input("Enter the city name or ZIP code: ").strip()
    get_weather_data(location, api_key)

if __name__ == "__main__":
    main()
