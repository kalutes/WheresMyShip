import sys
import os

def usage():
    print "Usage: python %s [filename]" % sys.argv[0]

def keywordLookup(lines):
    x = 0
    words = []
    fd = open("words.txt", "r")
    while True:
        words.append((fd.readline())[0:-1])
        if words[x] == '':
            del(words[x])
            break
        x += 1
    count = 0
    for n in xrange(0, len(lines)):
        org = lines[n]
        for x in xrange(0, len(words)):
            current = 0
            line = org
            for i in xrange(0, len(line)):
                current = line.find(words[x])
                if current > -1:
                    count += 1
                    line = line[(current + 1):len(line)]
                else:
                    break

    return count

def main():
    if len(sys.argv) != 2:
        usage()
        return
    fd = open(sys.argv[1], 'r')
    buff = []
    tempbuff = fd.readline()
    tempbuff = tempbuff[0:-1]
    while tempbuff != '':
        buff.append(tempbuff.lower())
        tempbuff = fd.readline()
    fd.closed
    score = keywordLookup(buff)
    filename = str(score) + "_" + sys.argv[1]
    os.system("mkdir -p ./priority")
    os.system("mv " + sys.argv[1] + " ./priority/" + filename)
    return

if __name__ == "__main__":
    main()
