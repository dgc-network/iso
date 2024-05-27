from flask import Flask, request, redirect, jsonify
import requests

app = Flask(__name__)

# Configuration variables (replace with your actual values)
TENANT_ID = 'your_tenant_id'
CLIENT_ID = 'your_client_id'
CLIENT_SECRET = 'your_client_secret'
REDIRECT_URI = 'https://your_redirect_uri'
SCOPE = 'https://api.businesscentral.dynamics.com/Financials.ReadWrite.All'

# Endpoint for OAuth callback
@app.route('/oauth-callback')
def oauth_callback():
    code = request.args.get('code')
    if code:
        # Exchange authorization code for access token
        token_endpoint = f"https://login.microsoftonline.com/{TENANT_ID}/oauth2/v2.0/token"
        token_data = {
            'client_id': CLIENT_ID,
            'client_secret': CLIENT_SECRET,
            'grant_type': 'authorization_code',
            'code': code,
            'redirect_uri': REDIRECT_URI,
            'scope': SCOPE
        }
        token_response = requests.post(token_endpoint, data=token_data)
        
        if token_response.status_code == 200:
            token_json = token_response.json()
            access_token = token_json.get('access_token')
            
            # Use the access token to retrieve data from the API
            endpoint_url = f"https://api.businesscentral.dynamics.com/v2.0/{TENANT_ID}/Production/ODataV4/Company('CRONUS%20USA%2C%20Inc.')/Chart_of_Accounts"
            headers = {
                'Authorization': f'Bearer {access_token}'
            }
            api_response = requests.get(endpoint_url, headers=headers)
            
            if api_response.status_code == 200:
                chart_of_accounts = api_response.json()
                return jsonify(chart_of_accounts)
            else:
                return f"API request failed with status code {api_response.status_code}", 500
        else:
            return f"Token request failed with status code {token_response.status_code}", 500
    else:
        return "Authorization code not found", 400

@app.route('/retrieve-chart-of-account')
def display_chart_of_accounts():
    # Authorization endpoint
    authorize_url = f"https://login.microsoftonline.com/{TENANT_ID}/oauth2/v2.0/authorize"
    authorization_params = {
        'client_id': CLIENT_ID,
        'response_type': 'code',
        'redirect_uri': REDIRECT_URI,
        'scope': SCOPE
    }
    authorization_url = requests.Request('GET', authorize_url, params=authorization_params).prepare().url
    
    # Redirect the user to the authorization URL
    return redirect(authorization_url)

if __name__ == '__main__':
    app.run(debug=True)
