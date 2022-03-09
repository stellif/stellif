import { Routes, Route } from "react-router-dom";
import useGlobalState from "./state";
import Posts from "./components/Posts";
import CreatePost from "./components/CreatePost";
import EditPost from "./components/EditPost";
import DeletePost from "./components/DeletePost";
import SignIn from "./components/SignIn";
import { useEffect } from "react";
import "./css/app.css";

function App() {
  const [token, setToken] = useGlobalState("token");

  useEffect(() => {
    if (localStorage.getItem("token")) {
      setToken(localStorage.getItem("token"));
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <div className="app">
      {token ? (
        <Routes>
          <Route path="/" element={<Posts />} />
          <Route path="/create-post" element={<CreatePost />} />
          <Route path="/edit-post/:id" element={<EditPost />} />
          <Route path="/delete-post/:id" element={<DeletePost />} />
        </Routes>
      ) : (
        <Routes>
          <Route path="/" element={<SignIn />} />
        </Routes>
      )}
    </div>
  );
}

export default App;
