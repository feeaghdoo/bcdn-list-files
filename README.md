Goes through bunny cdn dirs and recursively and lists files.
Edit config.json.template and rename to config.json.
Make dirs obj in config.json a dictionary of which subdirectories you want the script to be able to enumerate and the http get arg to have the script do that one.
Call script with ?type= (the arg defined in config.json).
Outputs json.
