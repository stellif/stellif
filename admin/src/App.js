import { Routes, Route } from "react-router-dom";
import useGlobalState from "./state";
import Posts from "./components/Posts";
import CreatePost from "./components/CreatePost";
import EditPost from "./components/EditPost";
import DeletePost from "./components/DeletePost";
import SignIn from "./components/SignIn";

function App() {
  const [token] = useGlobalState("token");

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
