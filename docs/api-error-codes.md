# API Error Codes

- **0**: Authentication failed. The `meta` table does not contain the given authentication token.
- **1**: Authentication failed. The `meta` table does contain a authentication token for a user, but that user does not exist.
- **2**: Authentication failed. An account with the provided e-mail does not exist.
- **3**: Authentication failed. Password is incorrect.
- **4**: Could not retrieve post. No post found with this ID.
- **5**: Could not update post. Validation failed.
- **6**: Could not update post. SQL Update failed.
- **7**: Could not update post. No such post found.