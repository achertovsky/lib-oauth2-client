# 1.0.0
## Added
- Fake for implementators
## Changed
- GoogleAuthenticator does not throws `Psr\Http\Client\ClientExceptionInterface`, its wrapped by `achertovsky\oauth\exception\OauthException`
## Fixed
- phpdoc

# 0.1.2
## Added
- Exception on google response is more verbose

# 0.1.1
## Fixed
- Request builder gonna expect json body instead of array

# 0.1.0
- initial version of lib created
