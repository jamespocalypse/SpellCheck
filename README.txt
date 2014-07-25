Generic class for suggesting alternative spellings of names based on a dictionary. An input
word is modified according to an alphabet to delete, insert, substitute, or transpose a 
single character at a time. This employs no smart filtering and simply returns a list of 
results which are at most on character change away from the input string.

ie. Correct word => Apple  Query word => Aple would return Apple as the suggested word