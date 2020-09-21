# Concept

The target is to present code sample of server side game called "Guess number" with simple API.

# Working with GIT

Implementation process should be committed to the provided repository, according to the following assumptions:

- `master` branch should be empty;
- `setup` branch (dependent of `master`) should contains only basic environment setup, without any details of the game;
- `game` branch (dependent of `setup`) should contains implementation details of the game;
- for both `setup` and `game` branches appropriative pull requests should be submitted;


# API schema

/new GET
/guess GET
/scores GET

All requests and responses should use `Content-Type: application/json` header.
When error occurs in any case, JSON of following shape should be returned:
```
{
  error: string        // error message
}
```

## endpoint /new

Provides `id` for new game, that can be later provided to `/guess` endpoint. 
Should handle following settings:
```
{
  playerName: string    // optional, default 'Unnamed player'
  from: number      // optional, default 1, greater than 0, greater than or equal `to` - 2
  to: number        // optional, default 9, greater than 2, greater than or equal 'from' + 2
  attempts: number  // optional, default 3, greater than 0
}
```

Should respond with JSON of following shape:
```
{
  id: string        // game ID
}
```


## endpoint /guess

This is major game part. Guessing randomly generated number for game created with `/new` endpoint. Endpoint should handle following parameters:
```
{
  id: string        // required, game ID
  number: number    // required, number to guess
}
```

- `number` should fit into requirements of `from` and `to` fields;
- If `id`, or `number` is not valid, suitable error message should be returned;
- After 5 minutes of inactivity, game shouldn't be available anymore;
- If game is already finished (won or lost), suitable error message should be returned.

Result should be JSON with conditional fields, depend on game state:
```
{
  status: GameStatus
  number: number
  score: number
  place: number
}
```
where `GameStatus` is enum field for values `"pending" | "won" | "lost"`

- `pending` - should be returned when guessed number is incorrect but there are still attempts to guess; other JSON fields should be omitted in this case;
- `won` - number has been guessed; score and place should be returned as well in this case;
- `lost` - number has not been guessed and there is no more attempt; number should be returned as well then;

When game is won, the score and place should be calculated based on the probability of guessing - the lower the probability, the higher place. There is no strict details of design pattern for scoring system, so it can be provided on programmer's will.
Top 30 scores should be saved to file (player name, score, place).


## endpoint /scores

Returns top 30 scores as JSON of following shape:

```{
  scores: Array<{
      playerName: string
      score: string
    }>
}
```

