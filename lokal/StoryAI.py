import requests
import json
import ollama
import sys

# Globaler Dialogverlauf
dialog_history = []
model = 'llama3'

def fetch_jira_stories(url):
    response = requests.get(url)
    if response.status_code == 200:
        stories = response.json()
        # Filtern der Daten, um nur den 'fields'-Teil jeder Story zu behalten
        filtered_stories = [{'fields': story['fields']} for story in stories]
        return filtered_stories
    else:
        raise Exception(f"Failed to fetch data: {response.status_code} {response.text}")

def write_stories_to_file(stories, filename):
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(stories, f, ensure_ascii=False, indent=4)

def read_stories_from_file(filename):
    with open(filename, 'r', encoding='utf-8') as f:
        return json.load(f)

def create_ollama_model(stories):
    stories_json = json.dumps(stories, ensure_ascii=False, indent=4)
    modelfile_content = f'''FROM {model}
    SYSTEM Backlog: {stories_json}
    '''
    ollama.create(model='Backlog', modelfile=modelfile_content)

def query_llm(text):
    global dialog_history
    if not dialog_history:  # Start dialog history if empty
        system_message = {"role": "system", "content": "Bitte antworte immer mit einer Scrum-User Story in deutscher Sprache im folgenden Format: 'fields': { 'summary': 'Title', 'description': '{\n  \'summary\': \'Als ... Nutzertyp möchte ich ... können, um ... zu erreichen.\',\n  \'description\': \'Akzeptanzkriterien: \\n1. Der Nutzer kann ...\\n2. ...'}"
        }
        dialog_history.append(system_message)

    dialog_history.append({"role": "user", "content": text})
    
    url = 'http://localhost:11434/api/chat'
    headers = {'Content-Type': 'application/json'}
    data = {
        "model": "Backlog",
        "messages": dialog_history,
        "format": "json",
        "stream": False
    }
    response = requests.post(url, json=data, headers=headers, stream=False)
    full_sentence = ""
    
    if response.status_code == 200:
        try:
            for line in response.iter_lines():
                if line:
                    decoded_line = json.loads(line.decode('utf-8'))
                    if decoded_line.get("done", False):
                        break
                    response_text = decoded_line.get('message', {}).get('content', '')
                    if response_text and response_text.strip():  # Überprüfung auf tatsächlichen Inhalt
                        full_sentence += response_text.strip()  # `.strip()` entfernt führende/abschließende Leerzeichen
                        if response_text.strip()[-1] in ".!?":
                            print(full_sentence)
                            dialog_history.append({"role": "assistant", "content": full_sentence})
                            full_sentence = ""
            return full_sentence.strip()  # Sicherstellen, dass keine zusätzlichen Leerzeichen zurückgegeben werden
        except json.JSONDecodeError as e:
            print("Fehler beim Parsen der JSON-Antwort:", e)
        except IndexError as e:
            print("IndexError, möglicherweise wegen fehlender Daten:", e)
    else:
        print(f"Error: {response.status_code}, Message: {response.text}")


if __name__ == "__main__":
    url = "https://scrummastersmind.com/getStories.php"
    filename = "jira_stories.json"

    try:
        jira_stories = fetch_jira_stories(url)
        write_stories_to_file(jira_stories, filename)
        stories = read_stories_from_file(filename)
        create_ollama_model(stories)
    except Exception as e:
        print(f"Problem with fetching or processing stories: {e}")
        sys.exit(1)

    # Receive user input and query LLM
    user_input = input("Bitte geben Sie Ihren Suchbegriff ein: ")
    response_story = query_llm(user_input)
    print("Response from LLM:", response_story)