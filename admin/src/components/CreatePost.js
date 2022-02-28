import { useEffect } from "react";
import { _post } from "../fetch";
import { useNavigate } from "react-router-dom";
import useGlobalState from "../state";

export default function CreatePost() {
  const [token, setToken] = useGlobalState("token");
  const navigate = useNavigate();

  useEffect(() => {
    const createPost = async () => {
      const response = await _post("post", {
        token,
      });

      // Auth failed
      if (response.data?.errorCode === 0) {
        localStorage.removeItem("token");
        setToken(false);
      }

      navigate(`/edit-post/${response.data.id}`, {
        replace: true,
      });
    };

    createPost();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return null;
}
