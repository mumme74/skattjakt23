import { useReducer, Reducer } from "react";

export enum eQuizState {
  NotStarted,
  InProgress,
  Finished,
}

export type Question = {
  id: number,
  str: string,
  points: number,
  correctAnswer: number | string | number[],
}

export type MultiQuestions = {
  choices: string[],
}

export type Answer = {
  id: number,
  questionId: number,
  answered: string,
  answeredBy: string,
  correct: boolean,
  points: number,
}

export type Group = {
  name: string,
  creator: string,
  members: string[],
  invites: string[],
  answers: Answer[],
}

export type GroupInvite = {
  group: Group,
  userName: string,
}

export type User = {
  userName: string;
  group: string;
}

// ----------------------------------------------------
// actions


export type LoginAction = {
  type: 'setLoginState',
  payload: boolean,
}

export type AuthTokenAction = {
  type: 'token',
  payload: string,
}


export type UserNameSetAction = {
  type: 'setUserName',
  payload: string,
}

export type UserNameClearAction = {
  type: 'clearUserName',
  payload: string,
}

export type QuizStateSetAction = {
  type: 'setQuizState',
  payload: eQuizState,
}

export type AcceptGroupInviteAction = {
  type: 'acceptInviteToGroup',
  payload: string, // group name
}

export type CreateGroupAction = {
  type: 'createGroup',
  payload: Group,
}

export type InviteMemberToGroupAction = {
  type: 'inviteToGroup',
  payload: GroupInvite, // member to invite
}

export type AddUserAction = {
  type: 'addUser',
  payload: User,
}

// collectionAction

export type AppAction =
  LoginAction |
  AuthTokenAction |
  UserNameSetAction |
  UserNameClearAction |
  QuizStateSetAction |
  CreateGroupAction |
  AcceptGroupInviteAction |
  InviteMemberToGroupAction |
  AddUserAction;

// ----------------------------------------------------
// appState store

const persistedStr = localStorage.getItem('state');
const persisted = JSON.parse(persistedStr||"{}");

export interface AppState {
  loginState: boolean,
  authToken: string,
  userName: string,
  quizState: eQuizState,
  groups: Group[],
  myGroup: string, // the name of my group
  allUsers: User[],
}

const initialState: AppState = {
  loginState: false,
  authToken: "",
  userName: "",
  quizState: eQuizState.NotStarted,
  groups: [],
  myGroup: "",
  allUsers: [],
  ...persisted
};


export const appState = {...initialState};


// ----------------------------------------------------
// reducer function

export const appReducer = (
  state: AppState, action: AppAction
): AppState => {
  switch(action.type) {
  case 'setLoginState':
    return {...state, loginState: action.payload};
  case 'token':
    return {...state, authToken: action.payload};
  case 'setUserName':
    return {...state, userName: action.payload};
  case 'clearUserName':
    return {...state, userName: ""};
  case 'setQuizState':
    return {...state, quizState: action.payload};
  case 'createGroup': {
    const o = structuredClone(state);
    o.groups.push(action.payload);
    return o;
  }
  case 'inviteToGroup': {
    const group = action.payload.group;
    const o = structuredClone(state);
    (o.groups as Group[]).find(g=>g.name===group.name)?.invites.push(action.payload.userName);
    //sendInvite(action.payload);
    return o;
  }
  case 'acceptInviteToGroup': {
    const group = action.payload;
    const o = structuredClone(state);
    (o.groups as Group[]).find(g=>g.name===group)?.members.push(state.userName);
    return o;
  }
  case 'addUser':
    return {
      ...state,
      allUsers: [...state.allUsers, action.payload]
    }
  default:
    return state;
  }
}

