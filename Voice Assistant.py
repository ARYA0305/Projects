import speech_recognition as sr
import pyttsx3
import datetime
import webbrowser
import wikipedia
recognizer = sr.Recognizer()
engine = pyttsx3.init()
engine.setProperty('rate', 150)

def speak(text):
    print("Assistant:", text)
    engine.say(text)
    engine.runAndWait()

def listen():
    with sr.Microphone() as source:
        print("Listening...")
        recognizer.adjust_for_ambient_noise(source)
        audio = recognizer.listen(source)

    try:
        command = recognizer.recognize_google(audio).lower()
        print("You:", command)
        return command
    except sr.UnknownValueError:
        speak("I apologize, I missed that.")
        return ""
    except sr.RequestError:
        speak("Sorry, speech service is down.")
        return ""

def process_command(command):
    if "hello" in command:
        speak("Hello! How can I help you?")
    
    elif "time" in command:
        current_time = datetime.datetime.now().strftime("%I:%M %p")
        speak(f"The time is {current_time}")
    
    elif "date" in command:
        current_date = datetime.datetime.now().strftime("%A, %B %d, %Y")
        speak(f"Today's date is {current_date}")
    
    elif "search" in command or "look up" in command or "find" in command:
        query = command.replace("search", "").replace("look up", "").replace("find", "")
        speak(f"Searching for {query}")
        webbrowser.open(f"https://www.google.com/search?q={query.strip()}")

    elif "wikipedia" in command:
        query = command.replace("wikipedia", "")
        speak(f"Searching Wikipedia for {query}")
        try:
            summary = wikipedia.summary(query, sentences=2)
            speak(summary)
        except wikipedia.exceptions.DisambiguationError as e:
            speak("Multiple results are found. Please be more specific about your query.")
        except Exception:
            speak("Could not find anything on Wikipedia.")
    
    elif "exit" in command or "quit" in command:
        speak("Goodbye! Deactivating voice assistant.")
        exit()
    else:
        speak("Sorry, I don't understand that yet.")
speak("Voice Assistant activated. Say something...")

while True:
    command = listen()
    if command:
        process_command(command)
            
