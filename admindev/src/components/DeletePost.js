import { useEffect } from "react";
import useGlobalState from "../state";
import { _delete } from "../fetch";
import { useNavigate, useParams } from "react-router-dom";

export default function DeletePost() {
  const [token] = useGlobalState("token");
  const { id } = useParams();
  const navigate = useNavigate();

  useEffect(() => {
    const deletePost = async () => {
      const response = await _delete(`post/${id}`, {
        token,
      });

      // Failed.
      if (response.data?.errorCode === 0) {
        alert(response.data.error);
        navigate("/", {
          replace: true,
        });
        return;
      }

      navigate("/", {
        replace: true,
      });
    };

    deletePost();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return null;
}
