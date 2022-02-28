import { useState } from "react";
import { _post } from "../fetch";
import useGlobalState from "../state";

export default function SignIn() {
  const [email, setEmail] = useState(false);
  const [password, setPassword] = useState(false);
  const [, setToken] = useGlobalState("token");

  /**
   * Authenticate the user
   */
  const authenticate = async () => {
    const response = await _post("authenticate", {
      email,
      password,
    });

    // Auth failed.
    if (response.data?.errorCode) {
      alert(response.data.error);
      return;
    }

    // Auth succeeded.
    localStorage.setItem("token", response.data.token);
    setToken(response.data.token);
  };

  return (
    <div>
      <input type="email" onInput={(e) => setEmail(e.target.value)} />
      <br />
      <input type="password" onInput={(e) => setPassword(e.target.value)} />
      <br />
      <button type="submit" onClick={() => authenticate()}>
        Sign In
      </button>
    </div>
  );
}
