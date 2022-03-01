import { createGlobalState } from "react-hooks-global-state";

const initialState = {
  token: localStorage.getItem("token"),
};

const { useGlobalState } = createGlobalState(initialState);

export default useGlobalState;
