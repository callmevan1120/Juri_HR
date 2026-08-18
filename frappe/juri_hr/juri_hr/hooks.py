app_name = "juri_hr"
app_title = "JURI HR"
app_publisher = "JURI HR"
app_description = "JURI HR custom features for Frappe HR"
app_email = "dev@jurihr.app"
app_license = "mit"
app_icon = "octicon octicon-person-fill"

# App will be listed in the bench apps list
required_apps = ["frappe", "hr"]

# Includes in <head>
app_include_css = []

# Whitelisted API methods available to the SPA (namespace juri_hr.*)
api = "juri_hr.api"

# Scheduled Tasks
scheduler_events = {
    "daily": [
        "juri_hr.juri_hr.scheduled.expire_qr_tokens",
    ],
}

fixtures = []

# Default print format for custom doctypes
default_print_format = "Juri Hr Print"
