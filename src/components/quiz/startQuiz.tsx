import React, { useReducer } from "react";
import { Button } from "react-bootstrap";
import {
  appReducer,
  appState,
  QuizStateSetAction,
  eQuizState
} from "../../state/reducers";
import { useNavigate } from "react-router-dom";
import { useCustomContext } from "../../state/custom.context";

export function StartQuiz() {

  const {state, dispatch} = useCustomContext();
  const {userName, quizState} = state;
  const navigate = useNavigate();

  const startQuiz = () => {
    dispatch({type: 'setQuizState', payload: eQuizState.InProgress});
  }

  return <>
    <h1>Quiz..</h1>
    {quizState===eQuizState.NotStarted ?
      <>
        <h3>Vill du starta quizet?</h3>
        <Button variant="primary" onClick={startQuiz}>Starta</Button>
      </>
    :
      <>
        <h3>${userName}, du har redan startat ditt quiz.</h3>
        <Button variant="info" onClick={()=>navigate('/trackProgress')}>Följ hur det går</Button>
      </>
    }
  </>;
}